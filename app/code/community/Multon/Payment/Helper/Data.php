<?php

class Multon_Payment_Helper_Data extends Mage_Core_Helper_Abstract
{
    const _VERIFY_SUCCESS = 1; // payment successful
    const _VERIFY_CANCEL = 2; // payment unsuccessful
    const _VERIFY_CORRUPT = 3; // wrong or corrupt response

    /**
     * Calculates reference number for bank payment
     *
     * @param string $refStr Input reference
     *
     * @return string reference number
     */
    public function calcRef($refStr)
    {

        $n = (string) $refStr;
        $w = array(7, 3, 1);

        $sl = $st = strlen($n);
        $total = 0;
        while ( $sl > 0 and substr($n, --$sl, 1) >= '0' ) {
            $total += substr($n, ($st - 1) - $sl, 1) * $w[($sl % 3)];
        }
        $c = ((ceil(($total / 10)) * 10) - $total);
        return $n . $c;
    }

}

