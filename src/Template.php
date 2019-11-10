<?php
namespace MinPlate;

class Template {
    protected $data = [];
    protected $blocks = [];
    protected $outputs = [];
    protected $blocks_context = [];
    protected $block_names = [];
    protected $template_dirs = [];

    public function __construct($template_path = './'){
        $this->add_path($template_path);
    }

    public function add_path($template_path){
        if( !is_dir($template_path) ){
            throw new \Exception("{$template_path} is not a directory", 1);
        }
        array_unshift($this->template_dirs, realpath($template_path));
    }

    public function assign(string $variable_name, $value){
        $this->data[$variable_name] = $value;
    }

    public function include(string $template_name){
        $template_file = $this->get_template_file($template_name);
        $output = ob_get_clean();
        if($output){
            $block = current($this->blocks_context);
            if($block){
                $this->blocks[$block][] = ['type' => 'raw', 'data' => $output];
            } else {
                $this->outputs[] = ['type' => 'raw', 'data' => $output];
            }
        }
        $this->__parse($template_file);
    }

    public function block(string $block_name){
        $output = ob_get_clean();
        if($output){
            $block = current($this->blocks_context);
            if($block){
                $this->blocks[$block][] = ['type' => 'raw', 'data' => $output];
            } else {
                $this->outputs[] = ['type' => 'raw', 'data' => $output];
            }
        }
        array_unshift($this->blocks_context, $block_name);
        $this->blocks[$block_name] = [];
        ob_start();
    }

    public function end_block(string $block_name){
        $current = current($this->blocks_context);
        if($current != $block_name){
            ob_clean();
            throw new \Exception("Block {$current} does not have ending code", 3);
        }
        array_shift($this->blocks_context);

        # push the block output to block data
        $output = ob_get_clean();
        if($output){
            $this->blocks[$block_name][] = ['type' => 'raw', 'data' => $output];
        }

        # check this block is in another block
        $block = current($this->blocks_context);
        if($block){
            $this->blocks[$block][] = ['type' => 'block', 'data' => $block_name];
            if( !in_array($block_name, $this->block_names) ){
                $this->block_names[] = $block_name;
            }
        } else {
            if( !in_array($block_name, $this->block_names) ){
                $this->outputs[] = ['type' => 'block', 'data' => $block_name];
                $this->block_names[] = $block_name;
            }
        }

        ob_start();
    }

    public function render(string $template_name, array $data = []){
        $this->data = array_merge($this->data, $data);
        $template_file = $this->get_template_file($template_name);
        $this->__parse($template_file);
        if($this->blocks_context){
            $names = implode(', ', $this->blocks_context);
            throw new \Exception("Block(s) {$names} do not have ending code", 5);
        }
        return $this->__generate_content($this->outputs);
    }

    protected function get_template_file($template_name){
        foreach($this->template_dirs as $path){
            $template_file = $path.DIRECTORY_SEPARATOR.$template_name;
            if(is_file($template_file)){
                return $template_file;
            }
        }

        throw new \Exception("Could not find template: {$template_name}", 4);
    }

    private function __parse($template_file){
        extract($this->data);
        ob_start();
        include($template_file);
        $output = ob_get_clean();
        if($output){
            $block = current($this->blocks_context);
            if($block){
                $this->blocks[$block][] = ['type' => 'raw', 'data' => $output];
            } else {
                $this->outputs[] = ['type' => 'raw', 'data' => $output];
            }
        }
        ob_start();
    }

    private function __generate_content($outputs){
        $html = '';

        foreach($outputs as $item){
            if($item['type'] == 'raw'){
                $html .= $item['data'];
            } else if ($item['type'] == 'block') {
                $html .= $this->__generate_content($this->blocks[$item['data']]);
            }
        }

        return $html;
    }
}
