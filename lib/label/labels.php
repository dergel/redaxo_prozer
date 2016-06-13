<?php

class pz_labels
{
    public static $labels = [];

    public static function get()
    {
        $sql = rex_sql::factory();
        $sql->setQuery('select * from pz_label order by name');
        $labels = [];
        foreach ($sql->getArray() as $l) {
            $label = new pz_label($l);
            $labels[$label->getId()] = $label;
        }
        return $labels;
    }

    public static function getAsString()
    {
        $return = [];
        foreach (pz_labels::get() as $label) {
            $v = $label->getName();
            $v = str_replace('=', '', $v);
            $v = str_replace(',', '', $v);
            $return[] = $v.'='.$label->getId();
        }
        return implode(',', $return);
    }

    public static function update()
    {
        $content = '';
        foreach (pz_labels::get() as $l) {
            $content .= "\n".'.labelc'.$l->getId().' { background-color:'.$l->getColor().' !important; }';
            $content .= "\n".'.labelb'.$l->getId().' { border-color:'.$l->getBorder().' !important; }';
        }
        //.labelc0 { background-color: #f60 !important; /* orange */ }
        //.labelc1 { background-color: #fcb819 !important; /* gelb (dunkel) */ }
        //.labelc2 { background-color: #e118fc !important; /* violett */ }
        //.labelc3 { background-color: #119194 !important; /* tuerkis */ }
        //.labelc4 { background-color: #678820 !important; /* gruen */ }
        //.labelc5 { background-color: #0f5dca !important; /* blau */ }
        //.labelc6 { background-color: #eb005f !important; /* rot (zart) */ }

        $file = rex_path::frontend('assets/addons/prozer/labels_screen.css');

        file_put_contents($file, $content);
    }
}
