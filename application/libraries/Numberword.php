<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Numberword
{
    // TODO
    // add to options
    // words without spaces
    // array of possible numbers => words
    private $word_array = array();
    // thousand array,
    private $thousand = array();
    // variables
    private $val;
    private $currency0;
    private $currency1;
    // codeigniter instance
    private $ci;
    private $val_array;
    private $dec_value;
    private $dec_word;
    private $num_value;
    private $num_word;
    private $val_word;
    private $original_val;

    public function __construct($params = array())
    {
        $l = '';
        $this->ci =& get_instance();
        if (is_numeric($params['clientid'])) {
            $client_language = get_client_default_language($params['clientid']);
            if (!empty($client_language)) {
                if (file_exists(APPPATH . 'language/' . $client_language)) {
                    $l = $client_language;
                }
            }
        }
        $language = $l;
        if ($language == '') {
            $language = get_option('active_language');
        }
        $this->ci->lang->load($language . '_num_words_lang', $language);

        if (file_exists(APPPATH . 'language/' . $language . '/custom_lang.php')) {
            $this->ci->lang->load('custom_lang', $language);
        }

        array_push($this->thousand, "");
        array_push($this->thousand, _l('num_word_thousand') . ' ');
        array_push($this->thousand, _l('num_word_million') . ' ');
        array_push($this->thousand, _l('num_word_billion') . ' ');
        array_push($this->thousand, _l('num_word_trillion') . ' ');
        array_push($this->thousand, _l('num_word_zillion') . ' ');
        for ($i = 1; $i < 100; $i++) {
            $this->word_array[$i] = _l('num_word_' . $i);
        }
        for ($i = 100; $i <= 900; $i = $i + 100) {
            $this->word_array[$i] = _l('num_word_' . $i);
        }
    }

    public function convert($in_val = 0, $in_currency0 = "", $in_currency1 = true)
    {
        $this->original_val = $in_val;
        $this->val       = $in_val;
        $this->currency0 = _l('num_word_' . mb_strtoupper($in_currency0, 'UTF-8'));

        // Currency not found
        if (strpos($this->currency0, 'num_word_') !== false) {
            $this->currency0 = '';
        }
        if ($in_currency1 == false) {
            $this->currency1 = '';
        } else {
            $this->currency1 = _l('num_word_cents');
        }
        // remove commas from comma separated numbers
        $this->val = abs(floatval(str_replace(",", "", $this->val)));
        if ($this->val > 0) {
            // convert to number format
            $this->val       = number_format($this->val, '2', ',', ',');
            // split to array of 3(s) digits and 2 digit
            $this->val_array = explode(",", $this->val);
            // separate decimal digit
            $this->dec_value = intval($this->val_array[count($this->val_array) - 1]);
            if ($this->dec_value > 0) {
                $w_and = _l('number_word_and');
                $w_and = ($w_and == " " ? "" : $w_and.=" ");
                // convert decimal part to word;
                $this->dec_word = $w_and . '' . $this->word_array[$this->dec_value] . " " . $this->currency1;
            }
            // loop through all 3(s) digits in VAL array
            $t              = 0;
            // initialize the number to word variable
            $this->num_word = "";

            for ($i = count($this->val_array) - 2; $i >= 0; $i--) {
                // separate each element in VAL array to 1 and 2 digits
                $this->num_value = intval($this->val_array[$i]);

                // if VAL = 0 then no word
                if ($this->num_value == 0) {
                    $this->num_word = " " . $this->num_word;
                }

                // if 0 < VAL < 100 or 2 digits
                elseif (strlen($this->num_value . "") <= 2) {
                    $this->num_word = $this->word_array[$this->num_value] . " " . $this->thousand[$t] . $this->num_word;
                    // add 'and' if not last element in VAL
                    if ($i == 1) {
                        $w_and = _l('number_word_and');
                        $w_and = ($w_and == " " ? "" : $w_and.=" ");
                        $this->num_word =  $w_and. '' . $this->num_word;
                    }
                }
                // if VAL >= 100, set the hundred word
                else {
                    @$this->num_word = $this->word_array[mb_substr($this->num_value, 0, 1) . "00"] . (intval(mb_substr($this->num_value, 1, 2)) > 0 ? (_l('number_word_and') != " " ? " " . _l('number_word_and') . " " : " ") : "") . $this->word_array[intval(mb_substr($this->num_value, 1, 2))] . " " . $this->thousand[$t] . $this->num_word;
                }
                $t++;
            }
            // add currency to word
            if (!empty($this->num_word)) {
                $this->num_word .= "" . $this->currency0;
            }
        }
        // join the number and decimal words
        $this->val_word = $this->num_word . " " . $this->dec_word;

        if (get_option('total_to_words_lowercase') == 1) {
            $final_val = trim(mb_strtolower($this->val_word, 'UTF-8'));
        } else {
            $final_val = trim($this->val_word);
        }

        $hook_data = array();

        $hook_data['formatted_value'] = $final_val;
        $hook_data['total'] = $this->original_val;
        $hook_data = do_action('before_return_num_word', $hook_data);

        return $hook_data['formatted_value'];
    }
}
