# filament-accounts — Requisitos de Billing a Implementar

Este documento lista as lacunas identificadas no pacote `rotaz/filament-accounts` (v1.0)
que precisam ser implementadas para suportar o fluxo completo de assinaturas do KICONTA.
Cada item descreve o problema atual, o comportamento esperado e a localização no pacote.

---

## 1. `SubscriptionStatus` — adicionar case `TRIALING`

**Arquivo:** `src/Enums/SubscriptionStatus.php`

**Problema atual:** O enum só possui `ACTIVE` e `CANCELLED`. Não existe estado de trial,
forçando o app a criar a subscription do plano free já como `ACTIVE` e usar `ends_at`
para simular o prazo do trial — o que mistura o conceito de "assinatura ativa" com
"período de avaliação".

**Comportamento esperado:**

```php
enum SubscriptionStatus: string {
    case TRIALING  = 'Trialing';
    case ACTIVE    = 'Active';
    case CANCELLED = 'Cancelled';
    case EXPIRED   = 'Expired';   // (opcional) período encerrado sem renovação
}
```

**Impacto:** `CanBilling::subscription()` filtra por `ACTIVE`, então assinaturas em trial
não são retornadas pela relação padrão. Precisará de ajuste (ver item 3).

---

## 2. `subscriptionEnded()` — considerar `trial_ends_at`

**Arquivo:** `src/Concerns/Billing/HasBillingModels.php:55`

**Problema atual:**

```php
public static function subscriptionEnded(): bool
{
    $subscription = filament()->getTenant()->subscription;
    if (! $subscription) return true;
    return Carbon::now()->isAfter($subscription->ends_at); // ignora trial_ends_at
}
```

O campo `trial_ends_at` existe na migration mas nunca é consultado. Quando a conta está
em trial, `ends_at` carrega o fim do trial, criando ambiguidade entre "fim do trial" e
"fim do período pago".

**Comportamento esperado:**

```php
public static function subscriptionEnded(): bool
{
    $subscription = filament()->getTenant()->currentSubscription(); // retorna trialing ou active
    if (! $subscription) return true;

    if ($subscription->status === SubscriptionStatus::TRIALING) {
        return Carbon::now()->isAfter($subscription->trial_ends_at);
    }

    return Carbon::now()->isAfter($subscription->ends_at);
}
```

---

## 3. `CanBilling::subscription()` — incluir status `TRIALING`

**Arquivo:** `src/CanBilling.php:18`

**Problema atual:**

```php
public function subscription(): HasOne
{
    return $this->hasOne(...)->where('status', SubscriptionStatus::ACTIVE)->orderBy('created_at', 'desc');
}
```

Filtra apenas `ACTIVE`, ignorando assinaturas em trial.

**Comportamento esperado:** Retornar a subscription mais recente cujo status seja
`ACTIVE` ou `TRIALING`. Adicionar método auxiliar `currentSubscription()` para
deixar o nome `subscription()` como está (compatibilidade) e adicionar o novo.

---

## 4. `Subscription::generatePayment()` — chave PIX hardcoded

**Arquivo:** `src/Subscription.php:113`

**Problema atual:**

```php
protected function generatePayment($invoice, $amount)
{
    $link = FormatterUtil::format_pix([
        'key' => 'acpte@g',   // chave PIX hardcoded, não configurável
        ...
    ]);
}
```

O método não é `abstract` nem `overridable` por config, impedindo a substituição
por uma integração real de gateway (PagBank, Stripe etc.).

**Comportamento esperado:**

```php
// Opção A — ler de config
'key' => config('filament-accounts.pix_key'),

// Opção B — método protected overridable (preferível)
protected function generatePayment(string $invoiceId, float $amount): array
{
    // implementação padrão com PIX via config
    // apps sobrescrevem via modelo customizado registrado com useSubscriptionModel()
}
```

---

## 5. `CreateSubscription` — suporte a planos gratuitos sem invoices

**Arquivo:** `src/Actions/CreateSubscription.php`

**Problema atual:** `$subscription->createInvoices()` é sempre chamado, gerando uma
fatura de R$0,00 para planos gratuitos — ruído no histórico e processamento desnecessário.

**Comportamento esperado:**

```php
if ($billingPlan->monthly_price > 0 || $billingPlan->yearly_price > 0) {
    $subscription->createInvoices();
}
```

Ou alternativamente, deixar o `createInvoices()` como hook que o app pode sobrescrever.

---

## 6. `CreateSubscription` — suporte a `trial_ends_at` e `TRIALING` status

**Arquivo:** `src/Actions/CreateSubscription.php`

**Problema atual:** Sempre cria com `status = ACTIVE`. Para planos com `trial = true`,
deveria criar com `status = TRIALING`, `trial_ends_at = now()->addDays(N)` e
`ends_at = null` (ou igual ao trial).

**Comportamento esperado:**

```php
$isTrial = $billingPlan->trial;
$subscription = $billable->subscriptions()->create([
    'status'        => $isTrial ? SubscriptionStatus::TRIALING : SubscriptionStatus::ACTIVE,
    'trial_ends_at' => $isTrial ? Carbon::now()->addDays(config('filament-accounts.trial_days', 30)) : null,
    'ends_at'       => $isTrial ? null : $end_at,
    ...
]);
```

---

## 7. `BillingPlan::$fillable` — incompleto

**Arquivo:** `src/BillingPlan.php`

**Problema atual:**

```php
protected $fillable = ['id'];   // apenas id
```

Todos os campos relevantes (`name`, `monthly_price`, `yearly_price`, `trial`,
`default`, `active`, `features`, `description`) não estão em `$fillable`,
impedindo criação/atualização via `::create()` ou `::fill()`.

**Comportamento esperado:** Incluir todos os campos da migration.

---

## 8. `TenantSubscriptionFilter` — redirect comentado

**Arquivo:** `src/Http/Middleware/TenantSubscriptionFilter.php`

**Problema atual:**

```php
/*if ($ended) {
    return redirect(filament()->getCurrentPanel()->getTenantBillingUrl(...));
}*/
```

O middleware não bloqueia nada — o redirect está comentado no próprio pacote.

**Comportamento esperado:** Redirect ativo por padrão, com exclusão automática da
rota de billing para evitar loop de redirect.

---

## 9. `Subscription::cancel()` — cancelamento imediato sem período de graça

**Arquivo:** `src/Subscription.php:66`

**Problema atual:**

```php
public function cancel(): void
{
    $this->status = SubscriptionStatus::CANCELLED;
    ...
    $this->save();
}
```

O cancelamento é imediato. O padrão de mercado é manter o acesso até o fim
do período já pago (`ends_at`).

**Comportamento esperado:**

```php
public function cancel(bool $immediately = false): void
{
    $this->status = SubscriptionStatus::CANCELLED;
    if ($immediately) {
        $this->ends_at = Carbon::now();
    }
    // se !$immediately, ends_at permanece como está (acesso até fim do período)
    ...
}
```

---

## 10. Listener de criação automática de subscription FREE

**Inexistente no pacote.**

Ao criar uma conta, nenhuma subscription é criada automaticamente. O app precisa
registrar seu próprio listener para `AccountCreated`. O pacote deveria fornecer
um listener configurável via `FilamentAccounts::createInitialSubscriptionWith(planId)`.

---

## 11. Job e Notification de encerramento de trial

**Inexistente no pacote.**

O pacote não fornece nenhum mecanismo de notificação de fim de trial.
Deveria expor uma `TrialEndingNotification` base e um `NotifyTrialEndingJob`
que o app possa agendar ou sobrescrever.

---

## 12. Abstração de gateway de pagamento

**Inexistente no pacote.**

`generatePayment()` gera apenas PIX com chave hardcoded. Não existe interface
para integrar gateways reais (PagBank, Stripe, Paddle etc.).

**Comportamento esperado:** Interface `PaymentGateway` com métodos:

```php
interface PaymentGateway {
    public function createCharge(Subscription $sub, SubscriptionInvoice $invoice): array;
    public function cancelSubscription(Subscription $sub): bool;
    public function handleWebhook(array $payload): void;
}
```

Registrado via `FilamentAccounts::usePaymentGateway(PagBankGateway::class)`.

---

## 13. `SubscriptionInvoice` — `$fillable` incompleto

**Arquivo:** `src/SubscriptionInvoice.php` (não lido, inferido pela migration)

O modelo `SubscriptionInvoice` provavelmente tem `$fillable` restrito, similar
ao `BillingPlan`. Verificar e incluir todos os campos da migration:
`invoice_id`, `type`, `subscription_id`, `payload`, `amount`, `status`,
`paid_at`, `due_at`.

---

## Resumo de Prioridades

| # | Item | Prioridade | Impacto no app |
|---|---|---|---|
| 4 | `generatePayment()` overridable | CRÍTICA | PagBank não funciona sem isso |
| 8 | Middleware redirect ativo | CRÍTICA | Bloqueio não funciona |
| 1 | `SubscriptionStatus::TRIALING` | ALTA | Trial sem estado próprio |
| 2 | `subscriptionEnded()` considera trial | ALTA | Lógica de expiração incorreta |
| 6 | `CreateSubscription` suporta trial | ALTA | Plano free mal representado |
| 5 | Sem invoices para plano free | MÉDIA | Ruído no histórico |
| 9 | `cancel()` com período de graça | MÉDIA | UX de cancelamento |
| 3 | `subscription()` inclui trialing | MÉDIA | Relação incompleta |
| 7 | `BillingPlan::$fillable` | MÉDIA | Mass assignment bloqueado |
| 12 | Abstração de gateway | ALTA | Integração PagBank |
| 10 | Listener de subscription FREE | BAIXA | App implementa por conta |
| 11 | Job/Notification de trial | BAIXA | App implementa por conta |
| 13 | `SubscriptionInvoice::$fillable` | BAIXA | Verificar ao implementar |