<?php
declare(strict_types=1);
class Template
{
    private $config = array();
    private $grammar = array();
    private $label = array();
    private $lang = '';
    protected $values = array();

    public function __construct($lang, $config, $grammar, $label)
    {
        $this->config = $config;
        $this->grammar = $grammar;
        $this->label = $label;
        $this->lang = $lang;
    }

    public function set($key, $values)
    {
        $this->values[$key] = $values;
    }

    public function login($tpl_name) :string
    {
        $data = file_get_contents($tpl_name);
        $data = preg_replace_callback("/{TPL=\"(.*)\"}/Umis", 'Template::replace_tpl', $data);
        $data = preg_replace_callback("/{CFG=\"(.*)\"}/Umis", 'Template::replace_cfg', $data);
        $data = preg_replace_callback("/{GRMR=\"(.*)\"}/Umis", 'Template::replace_grammar', $data);
        $data = preg_replace_callback("/{LBL=\"(.*)\"}/Umis", 'Template::replace_label', $data);
        $data = preg_replace_callback("/{VAR=\"(.*)\"}/Umis", 'Template::replace_var', $data);

        return $data;

    }

    public function main($tpl_name) :string
    {
        $data = file_get_contents($tpl_name);
        $data = preg_replace_callback("/{TPL=\"(.*)\"}/Umis", 'Template::replace_tpl', $data);
        $data = preg_replace_callback("/{CFG=\"(.*)\"}/Umis", 'Template::replace_cfg', $data);
        $data = preg_replace_callback("/{GRMR=\"(.*)\"}/Umis", 'Template::replace_grammar', $data);
        $data = preg_replace_callback("/{LBL=\"(.*)\"}/Umis", 'Template::replace_label', $data);
        $data = preg_replace_callback("/{VAR=\"(.*)\"}/Umis", 'Template::replace_var', $data);
        $data = preg_replace_callback("/{CVARE=\"(.*)\"\s+pre=\"(.*)\"\s+post=\"(.*)\"}/Umis", 'Template::replace_cvare', $data);
        $data = preg_replace_callback("/{CVART=\"(.*)\"\s+pre=\"(.*)\"\s+post=\"(.*)\"\s+pre=\"(.*)\"\s+post=\"(.*)\"\s+pre=\"(.*)\"\s+post=\"(.*)\"\s+pre=\"(.*)\"\s+post=\"(.*)\"}/Umis", 'Template::replace_cvart', $data);

        return $data;
    }

    //Function replacing config placeholders
    private function replace_cfg(array $match) :string
    {
        $parameter_name = $match[1];

        if (isset($this->config[$this->lang][$parameter_name])) {
            return $this->config[$this->lang][$parameter_name];
        }
        else {
            throw new Exception('Config parameter [' . $parameter_name . '] not found!');
        }
    }

    //Function replacing grammar placeholders
    private function replace_grammar(array $match) : string
    {
        $parameter_name = $match[1];

        if (isset($this->grammar[$this->lang][$parameter_name])) {
            return $this->grammar[$this->lang][$parameter_name];
        }
        else {
            throw new Exception('Grammar parameter [' . $parameter_name . '] not found!');
        }
    }

    //Function replacing label placeholders
    private function replace_label(array $match) : string
    {
        $parameter_name = $match[1];

        if (isset($this->label[$this->lang][$parameter_name])) {
            return $this->label[$this->lang][$parameter_name];
        }
        else {
            throw new Exception('Label [' . $parameter_name . '] not found!');
        }
    }

    //Function replacing variable placeholders
    private function replace_var(array $match) :string
    {
        $parameter_name = $match[1];

        if (isset($this->values[$parameter_name])) {
            return strval($this->values[$parameter_name]);
        }
        else {
            throw new Exception('Dynamic value [' . $parameter_name . '] not found!');
        }
    }

    //Function replacing cycling extension variable placeholders
    private function replace_cvare(array $match) :string
    {
        $cvare_name = $match[1];
        $parameter_pre = $match[2];
        $parameter_post = $match[3];

        if (isset($this->values[$cvare_name])) {
            if (!is_array($this->values[$cvare_name])) {
                throw new Exception('Dynamic cycle value [' . $cvare_name . '] is not an array!');
            }
            else {
                $res = '';
                foreach ($this->values[$cvare_name] as $value) {
                    $res .= $value[0];
                    $res .= $parameter_pre;
                    $res .= $value[1];
                    $res .= $parameter_post;

                }
                return $res;
            }
        }
        else {
            throw new Exception('Dynamic cycle value [' . $cvare_name . '] not found!');
        }
    }

    //Function replacing template placeholders
    private function replace_tpl(array $match) : string
    {
        $file_name = 'Templates/' . $match[1];

        if (is_file($file_name)) {
            return file_get_contents($file_name);
        }
        else {
            throw new Exception('Subtemplate [' . $file_name . '] not found!');
        }
    }

    //Function replacing cycling variable in table placeholders
    private function replace_cvart(array $match) :string
    {
        $cvart_name = $match[1];
        $parameter_1_pre = $match[2];
        $parameter_1_post = $match[3];
        $parameter_2_pre = $match[4];
        $parameter_2_post = $match[5];
        $parameter_3_pre = $match[6];
        $parameter_3_post = $match[7];
        $parameter_4_pre = $match[8];
        $parameter_4_post = $match[9];

        if (isset($this->values[$cvart_name])) {
            if (!is_array($this->values[$cvart_name])) {
                throw new Exception('Dynamic cycle value [' . $cvart_name . '] is not an array!');
            }
            else {
                $res = '';
                foreach ($this->values[$cvart_name] as $value) {
                    $res .= $parameter_1_pre;
                    $res .= $value[0];
                    $res .= $parameter_1_post.PHP_EOL;
                    $res .= $parameter_2_pre;
                    $res .= $value[1];
                    $res .= $parameter_2_post.PHP_EOL;
                    $res .= $parameter_3_pre;
                    $res .= $value[0];
                    $res .= $parameter_3_post.PHP_EOL;
                    $res .= $parameter_4_pre;
                    $res .= $value[0];
                    $res .= $parameter_4_post.PHP_EOL;
                }
                return $res;
            }
        }
        else {
            throw new Exception('Dynamic cycle value [' . $cvart_name . '] not found!');
        }
    }
}