<?php

/**
 * Помощник для работы с телефонными номерами
 * Код старый
 *
 * @author neon
 * @Service("helperPhone")
 */
class Helper_Phone
{
    /**
     * Длина номера мобильного телефона
     *
     * @var integer
     */
    public $mobileLength = 11;

    /**
     * Поиск в строке номера мобильного телефона
     *
     * @param string $str
     * @tutorial
     * 		parseMobile ("+7 123 456 78 90") = 71234567890
     * 		parseMobile ("8-123(456)78 90") = 71234567890
     * 		parseMobile ("61-61-61") = false
     * @return string|false Номер телефона или false.
     */
    public function parseMobile($str)
    {
        if (strlen($str) < $this->mobileLength) {
            return false;
        }
        $i = 0;
        $c = $str[0];
        $result = "";
        if ($c == "+") {
            $i = 1;
        } else if ($c == "8") {
            // Россия, номер начинается с 8
            $i      = 1;
            $result = "7";
        }
        $digits  = "0123456789";
        $ignores = "-() +";
        for (; $i < strlen($str); ++$i) {
            $c = $str [$i];
            if (strpos($digits, $c) !== false) {
                $result .= $c;
            } else if (strpos($ignores, $c) === false) {
                return false;
            }
        }
        return (strlen($result) == $this->mobileLength) ? $result : false;
    }
}