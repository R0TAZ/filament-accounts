<?php

namespace App\Actions\FilamentAccounts;

use App\Models\Company;
use Rotaz\FilamentAccounts\Contracts\DeletesCompanies;

class DeleteCompany implements DeletesCompanies
{
    /**
     * Delete the given company.
     */
    public function delete(Company $company): void
    {
        $company->purge();
    }
}
