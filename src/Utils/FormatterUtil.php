<?php

namespace Rotaz\FilamentAccounts\Utils;

class FormatterUtil
{
    public static function format_invoice_id($subscription_id, $sequence, $ts = 1): string
    {
        return str_pad(strval($subscription_id), 4, '0', STR_PAD_LEFT)
            . '-' . str_pad(strval($ts), 6, '0', STR_PAD_LEFT)
            . '-' . str_pad(strval($sequence), 2, '0', STR_PAD_LEFT);

    }

    public static function format_currency($value, $currency = 'R$'): string
    {
        return $currency . ' ' . number_format($value, 2, ',', '.');
    }

    public static function format_pix($data): string
    {

        $result = '000201';
        $result .= self::format_pix_field('26', '0014br.gov.bcb.pix' . self::format_pix_field('01', $data['key']));
        $result .= '52040000'; // Código fixo
        $result .= '5303986';  // Moeda (Real)
        if ($data['amount'] > 0) {
            $result .= self::format_pix_field('54', number_format($data['amount'], 2, '.', ''));
        }
        $result .= '5802BR'; // País
        $result .= '5901N';  // Nome
        $result .= '6001C';  // Cidade
        $result .= self::format_pix_field('62', self::format_pix_field('05', $data['invoice_id']));
        $result .= '6304'; // Início do CRC16
        $result .= self::format_pix_crc16($result); // Adiciona o CRC16 ao final

        return $result;
    }

    public static function format_pix_field($id, $valor): string
    {
        return $id . str_pad(strlen($valor), 2, '0', STR_PAD_LEFT) . $valor;
    }

    public static function format_pix_crc16($fields): string
    {

        $result = 0xFFFF;

        for ($i = 0; $i < strlen($fields); $i++) {
            $result ^= (ord($fields[$i]) << 8);
            for ($j = 0; $j < 8; $j++) {
                if ($result & 0x8000) {
                    $result = ($result << 1) ^ 0x1021;
                } else {
                    $result <<= 1;
                }
                $result &= 0xFFFF;
            }
        }

        return strtoupper(str_pad(dechex($result), 4, '0', STR_PAD_LEFT));

    }
}
