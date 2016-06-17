<?php

class rex_yform_pz_address_fields extends rex_yform_abstract
{
    public $pz_address_fields = [];

    public function enterObject()
    {

        /*
        -------- TODO
        - Geburtstag
        - X-SOCIALPROFILE: twitter, facebook etc.
        - IMPP: skype, icq etc.
        - X-ABDATE
        - X-ABRELATEDNAMES
        - ...

        - ADR: Format / de/ ...
        - value_type und prefered noch einbauen

        */

        $html_id = $this->getHTMLId();
        $name = $this->getName();
        $fragment = $this->params['fragment'];

        $postaddresses = [];
        $phones = [];
        $emails = [];
        $urls = [];
        $socialprofiles = [];
        $impps = [];
        $phones_add_button = '';
        $emails_add_button = '';

        $phone_labels = ['WORK','HOME','CELL','WORK,FAX','HOME,FAX','iPhone','PAGER','MAIN'];
        $email_labels = ['WORK','HOME','iPhone','MobileMe'];
        $postaddress_labels = ['WORK','HOME'];
        $postaddress_fields = [
            2 => pz_i18n::msg('street'),
            0 => pz_i18n::msg('address_extra1'),
            1 => pz_i18n::msg('address_extra2'),
            5 => pz_i18n::msg('zip'),
            3 => pz_i18n::msg('city'),
            4 => pz_i18n::msg('region'),
            6 => pz_i18n::msg('country'),
        ];
        $url_labels = ['WORK','HOME','_$!<HomePage>!$_','MobileMe'];

        // X-SOCIALPROFILE
        $socialprofile_value_types = ['myspace','linkedin','flickr','facebook','twitter','plus.google.com','xing'];

        // IMPP
        $impp_labels = ['WORK','HOME'];
        $impp_value_types = [
            'AIM' => 'aim',
            'Facebook' => 'x-apple',
            'GaduGadu' => 'x-apple',
            'GoogleTalk' => 'xmpp',
            'ICQ' => 'aim',
            'Jabber' => 'xmpp',
            'MSN' => 'msnim',
            'QQ' => 'x-apple',
            'Skype' => 'skype',
            'Yahoo' => 'ymsgr',
        ];

        /*
        type 				label 	preferred 	value_type 		value
        X-SOCIALPROFILE 			0 			facebook 		http://facebook.com/test 	editieren 	- löschen
        IMPP 				WORK 	0 			ICQ 			aim:163252327
        */


		// wenn nicht send == 1 und main id .. 

		if ($this->params['send'] != 1 && $this->params['main_id'] != '' && $address = pz_address::get($this->params['main_id'])) {
            foreach ($address->getFields() as $field) {
                switch ($field->getVar('type')) {
                    case('ADR'):  $postaddresses[] = [
                        'value' => $field->getVar('value'),
                        'label' => $field->getVar('label'),
                        'preferred' => $field->getVar('preferred'),
                        'value_type' => $field->getVar('value_type'),
                    ]; break;
                    case('TEL'):  $phones[] = [
                        'value' => $field->getVar('value'),
                        'label' => $field->getVar('label'),
                        'preferred' => $field->getVar('preferred'),
                        'value_type' => $field->getVar('value_type'),
                    ]; break;
                    case('EMAIL'):$emails[] = [
                        'value' => $field->getVar('value'),
                        'label' => $field->getVar('label'),
                        'preferred' => $field->getVar('preferred'),
                        'value_type' => $field->getVar('value_type'),
                    ]; break;
                    case('URL'):  $urls[] = [
                        'value' => $field->getVar('value'),
                        'label' => $field->getVar('label'),
                        'preferred' => $field->getVar('preferred'),
                        'value_type' => $field->getVar('value_type'),
                    ]; break;
                    case('X-SOCIALPROFILE'):  $socialprofiles[] = [
                        'value' => $field->getVar('value'),
                        'label' => $field->getVar('label'),
                        'preferred' => $field->getVar('preferred'),
                        'value_type' => $field->getVar('value_type'),
                    ]; break;
                    case('IMPP'): {
                        $v = explode(':', $field->getVar('value'));
                        $impps[] = [
                            'value' => $field->getVar('value'),
                            'exploded_value' => $v[1],
                            'label' => $field->getVar('label'),
                            'preferred' => $field->getVar('preferred'),
                            'value_type' => $field->getVar('value_type'),
                        ]; break;
                    }

                    // default: 	  $other[] = array("value"=>$field->getVar("value"),"label" => $field->getVar("label")); break;
                }
            }

        } else {
        
            // TEL
            $phone_field_labels = rex_request('address_field_phone_label', 'array');
            $phone_field_values = rex_request('address_field_phone_value', 'array');
            foreach ($phone_field_values as $k => $v) {
                if ($phone_field_values[$k] != '') {
                    $phones[] = [
                        'value' => $phone_field_values[$k],
                        'label' => $phone_field_labels[$k],
                        'preferred' => 0,
                        'value_type' => 'VOICE',
                    ];
                }
            }

            // EMAIL
            $email_field_labels = rex_request('address_field_email_label', 'array');
            $email_field_values = rex_request('address_field_email_value', 'array');
            foreach ($email_field_values as $k => $v) {
                if ($email_field_values[$k] != '') {
                    $emails[] = [
                        'value' => $email_field_values[$k],
                        'label' => $email_field_labels[$k],
                        'preferred' => 0,
                        'value_type' => '',
                    ];
                }
            }

            // ADR
            $postaddress_field_labels = rex_request('address_field_postaddress_label', 'array');
            $postaddress_field_values = [];
            for ($i = 0;$i < count($postaddress_fields);$i++) {
                $postaddress_field_values[$i] = rex_request('address_field_postaddress_value_'.$i, 'array');
            }

            for ($i = 0;$i < count($postaddress_field_labels);$i++) {
                $value = [];
                $save = false;
                for ($j = 0;$j < count($postaddress_fields);$j++) {
                    if ($postaddress_field_values[$j][$i] != '') {
                        $save = true;
                    }
                    $value[] = str_replace(';', '', $postaddress_field_values[$j][$i]);
                }
                if ($save) {
                    $postaddresses[] = [
                        'value' => implode(';', $value),
                        'label' => $postaddress_field_labels[$i],
                        'preferred' => 0,
                        'value_type' => 'de',
                    ];
                }
            }

            // URL
            $url_field_labels = rex_request('address_field_url_label', 'array');
            $url_field_values = rex_request('address_field_url_value', 'array');
            foreach ($url_field_values as $k => $v) {
                if ($url_field_values[$k] != '') {
                    $urls[] = [
                        'value' => $url_field_values[$k],
                        'label' => $url_field_labels[$k],
                        'preferred' => 0,
                        'value_type' => '',
                    ];
                }
            }

            // X-SOCIALPROFILE
            $socialprofile_field_value_types = rex_request('address_field_socialprofile_value_type', 'array');
            $socialprofile_field_values = rex_request('address_field_socialprofile_value', 'array');
            foreach ($socialprofile_field_values as $k => $v) {
                if ($socialprofile_field_values[$k] != '') {
                    $socialprofiles[] = [
                        'label' => '',
                        'preferred' => 0,
                        'value_type' => $socialprofile_field_value_types[$k],
                        'value' => $socialprofile_field_values[$k],
                    ];
                }
            }

            // IMPP
            $impp_field_labels = rex_request('address_field_impp_label', 'array');
            $impp_field_value_types = rex_request('address_field_impp_value_type', 'array');
            $impp_field_values = rex_request('address_field_impp_value', 'array');
            foreach ($impp_field_values as $k => $v) {
                if ($impp_field_values[$k] != '') {
                    $v = $impp_value_types[$impp_field_value_types[$k]].':'.$impp_field_values[$k];
                    $impps[] = [
                        'label' => $impp_field_labels[$k],
                        'preferred' => 0,
                        'value_type' => $impp_field_value_types[$k],
                        'value' => $v,
                        'exploded_value' => $impp_field_values[$k],
                    ];
                }
            }
            
        } 
        

        $output = '<div class="split-h"></div>';

        // Phone

        $f = new pz_fragment();
        $f->setVar('before', '', false);
        $f->setVar('after', '', false);
        $f->setVar('extra', '', false);
        $f->setVar('name', $name, false);
        $f->setVar('class', 'phone_field', false);

		$phone_blocks = [];
		
        foreach ($phones as $phone) {
            $select = new rex_select();
            $select->setSize(1);
            $select->setStyle('width:80px;');
            $select->setName('address_field_phone_label[]');
            foreach ($phone_labels as $label) {
                $select->addOption($label, $label);
            }

            if (!in_array($phone['label'], $phone_labels)) {
                $select->addOption($phone['label'], $phone['label']);
            }
            $select->setSelected($phone['label']);

            $label = '<label class="'.$this->getHTMLClass().'">' . $select->get() . '</label>';
            $field = '<input class="'.$this->getHTMLClass().'" type="text" name="address_field_phone_value[]" value="'.htmlspecialchars($phone['value']).'" />';
            $f->setVar('label', $label, false);
            $f->setVar('field', $field, false);
            $f->setVar('class', 'phone_field', false);
            $phone_blocks[] = $f->parse($fragment);
        }

        $select = new rex_select();
        $select->setSize(1);
        $select->setStyle('width:80px;');
        $select->setName('address_field_phone_label[]');
        foreach ($phone_labels as $label) {
            $select->addOption($label, $label);
        }
        $label = '<label class="'.$this->getHTMLClass().'" >' . $select->get() . '</label>';
        $field = '<input class="'.$this->getHTMLClass().'" type="text" name="address_field_phone_value[]" value="" />';
        $f->setVar('label', $label, false);
        $f->setVar('field', $field, false);
        $f->setVar('html_id', $this->getHTMLId('phone_hidden'), false);
        $phones_hidden_block = '<div id="'.$this->getHTMLId('phone_hidden_div').'" class="hidden">'.$f->parse($fragment).'</div>';
        $phones_add_block = '<div>'.$f->parse($fragment).'</div>';

        $field = '<a class="bt5" href="javascript:void(0);" onclick="
						inp = $(\'#'.$this->getHTMLId('phone_hidden').'\').clone();
						inp.attr({ id: \'\' });
						$(\'#'.$this->getHTMLId('phone_hidden_div').'\').before(inp);
						">+ '.pz_i18n::msg('add_phonefield').'</a>';
        $f = new pz_fragment();
        $f->setVar('label', '<label></label>', false);
        $f->setVar('field', $field, false);
        $phones_add_button .= $f->parse($fragment);

        $phones_output = '<h2 class="hl2">' . pz_i18n::msg('address_phone') . '</h2>';
        $phones_output .= implode("",$phone_blocks);
        $phones_output .= $phones_add_block;
        $phones_output .= $phones_hidden_block;
        $phones_output .= $phones_add_button;

        $output = '<div class="split-h"></div>'.'<div id="pz_address_fields_phone">'.$phones_output.'</div>'.'<div class="split-h"></div>';


        // Email

        $f = new pz_fragment();
        $f->setVar('name', $name, false);
        $f->setVar('class', 'email_field', false);

		$email_blocks = [];
        foreach ($emails as $email) {
            $select = new rex_select();
            $select->setSize(1);
            $select->setStyle('width:80px;');
            $select->setName('address_field_email_label[]');
            foreach ($email_labels as $label) {
                $select->addOption($label, $label);
            }

            if (!in_array($email['label'], $email_labels)) {
                $select->addOption($email['label'], $email['label']);
            }
            $select->setSelected($email['label']);

            $label = '<label class="'.$this->getHTMLClass().'">' . $select->get() . '</label>';
            $field = '<input class="'.$this->getHTMLClass().'" type="text" name="address_field_email_value[]" value="'.htmlspecialchars($email['value']).'" />';
            $f->setVar('label', $label, false);
            $f->setVar('field', $field, false);
            $f->setVar('class', 'email_field', false);
            $email_blocks[] = $f->parse($fragment);
        }

        $select = new rex_select();
        $select->setSize(1);
        $select->setStyle('width:80px;');
        $select->setName('address_field_email_label[]');
        foreach ($email_labels as $label) {
            $select->addOption($label, $label);
        }
        $label = '<label class="'.$this->getHTMLClass().'" >' . $select->get() . '</label>';
        $field = '<input class="'.$this->getHTMLClass().'" type="text" name="address_field_email_value[]" value="" />';
        $f->setVar('label', $label, false);
        $f->setVar('field', $field, false);
        $f->setVar('html_id', $this->getHTMLId('email_hidden'), false);
        $emails_hidden_block = '<div id="'.$this->getHTMLId('email_hidden_div').'" class="hidden">'.$f->parse($fragment).'</div>';
		$emails_add_block = '<div>'.$f->parse($fragment).'</div>';

        $field = '<a class="bt5" href="javascript:void(0);" onclick="
						inp = $(\'#'.$this->getHTMLId('email_hidden').'\').clone();
						inp.attr({ id: \'\' });
						$(\'#'.$this->getHTMLId('email_hidden_div').'\').before(inp);
						">+ '.pz_i18n::msg('add_emailfield').'</a>';
        $f = new pz_fragment();
        $f->setVar('label', '<label></label>', false);
        $f->setVar('field', $field, false);
        $emails_add_button .= $f->parse($fragment);

        $emails_output = '<h2 class="hl2">' . pz_i18n::msg('address_email') . '</h2>';
		$emails_output .= implode("",$email_blocks);
		$emails_output .= $emails_add_block;
		$emails_output .= $emails_hidden_block;
		$emails_output .= $emails_add_button;

        $output .= '<div id="pz_address_fields_email">'.$emails_output.'</div>'.'<div class="split-h"></div>';

        // postaddresse

        $postaddresses_output = '<h2 class="hl2">' . pz_i18n::msg('address_postaddress') . '</h2>';
        foreach ($postaddresses as $postaddress) {
            $select = new rex_select();
            $select->setSize(1);
            $select->setStyle('width:80px;');
            $select->setName('address_field_postaddress_label[]');
            foreach ($postaddress_labels as $label) {
                $select->addOption($label, $label);
            }

            if (!in_array($postaddress['label'], $postaddress_labels)) {
                $select->addOption($postaddress['label'], $postaddress['label']);
            }
            $select->setSelected($postaddress['label']);

            $f = new pz_fragment();
            $f->setVar('name', $name, false);
            $f->setVar('class', 'postaddress_field', false);
            $label = '<label class="'.$this->getHTMLClass().'">' . pz_i18n::msg('address_type') . '</label>';
            $field = $select->get();
            $f->setVar('label', $label, false);
            $f->setVar('field', $field, false);
            $f->setVar('class', 'postaddresse_field', false);
            $postaddresses_output .= $f->parse($fragment);

            $val = explode(';', $postaddress['value']);
            foreach ($postaddress_fields as $k => $af) {
                $f = new pz_fragment();
                $f->setVar('name', $name, false);
                $f->setVar('class', 'postaddress_field', false);
                $label = '<label class="'.$this->getHTMLClass().'">' . $af . '</label>';
                $field = '<input class="'.$this->getHTMLClass().'" type="text" name="address_field_postaddress_value_'.$k.'[]" value="'.htmlspecialchars($val[$k]).'" />';
                $f->setVar('label', $label, false);
                $f->setVar('field', $field, false);
                $f->setVar('class', 'postaddresse_field', false);
                $postaddresses_output .= $f->parse($fragment);
            }
            $postaddresses_output .= '<div class="split-h"></div>';
        }

        $postaddress_output = '';
        $select = new rex_select();
        $select->setSize(1);
        $select->setStyle('width:80px;');
        $select->setName('address_field_postaddress_label[]');
        foreach ($postaddress_labels as $label) {
            $select->addOption($label, $label);
        }
        $f = new pz_fragment();
        $f->setVar('name', $name, false);
        $f->setVar('class', 'postaddress_field', false);
        $label = '<label class="'.$this->getHTMLClass().'">' . pz_i18n::msg('address_type') . '</label>';
        $field = $select->get();
        $f->setVar('label', $label, false);
        $f->setVar('field', $field, false);
        $f->setVar('class', 'postaddresse_field', false);
        $postaddress_output .= $f->parse($fragment);

        foreach ($postaddress_fields as $k => $af) {
            $f = new pz_fragment();
            $f->setVar('name', $name, false);
            $f->setVar('class', 'postaddress_field', false);
            $label = '<label class="'.$this->getHTMLClass().'">' . $af . '</label>';
            $field = '<input class="'.$this->getHTMLClass().'" type="text" name="address_field_postaddress_value_'.$k.'[]" value="" />';
            $f->setVar('label', $label, false);
            $f->setVar('field', $field, false);
            $f->setVar('class', 'postaddresse_field', false);
            $postaddress_output .= $f->parse($fragment);
        }
        $postaddress_output = '<div id="'.$this->getHTMLId('postaddress_hidden').'">'.$postaddress_output.'<div class="split-h"></div></div>';
        $postaddresses_output .= '<div id="'.$this->getHTMLId('postaddress_hidden_div').'" class="hidden">'.$postaddress_output.'</div>';

        $field = '<a class="bt5" href="javascript:void(0);" onclick="
						inp = $(\'#'.$this->getHTMLId('postaddress_hidden').'\').clone();
						inp.attr({ id: \'\' });
						$(\'#'.$this->getHTMLId('postaddress_hidden_div').'\').before(inp);
						">+ '.pz_i18n::msg('add_postaddressfield').'</a>';
        $f = new pz_fragment();
        $f->setVar('label', '<label></label>', false);
        $f->setVar('field', $field, false);
        $postaddresses_output .= $f->parse($fragment);
        $postaddresses_output = '<div id="pz_address_fields_postaddresses">'.$postaddresses_output.'</div>';

        $output .= $postaddresses_output.'<div class="split-h"></div>';

        // Url

        $f = new pz_fragment();
        $f->setVar('name', $name, false);
        $f->setVar('class', 'url_field', false);

		$url_blocks = [];
        foreach ($urls as $url) {
            $select = new rex_select();
            $select->setSize(1);
            $select->setStyle('width:80px;');
            $select->setName('address_field_url_label[]');
            foreach ($url_labels as $label) {
                $select->addOption($label, $label);
            }

            if (!in_array($url['label'], $url_labels)) {
                $select->addOption($url['label'], $url['label']);
            }
            $select->setSelected($url['label']);

            $label = '<label class="'.$this->getHTMLClass().'">' . $select->get() . '</label>';
            $field = '<input class="'.$this->getHTMLClass().'" type="text" name="address_field_url_value[]" value="'.htmlspecialchars($url['value']).'" />';
            $f->setVar('label', $label, false);
            $f->setVar('field', $field, false);
            $f->setVar('class', 'url_field', false);
            $url_blocks[] = $f->parse($fragment);
        }

        $select = new rex_select();
        $select->setSize(1);
        $select->setStyle('width:80px;');
        $select->setName('address_field_url_label[]');
        foreach ($url_labels as $label) {
            $select->addOption($label, $label);
        }
        $label = '<label class="'.$this->getHTMLClass().'" >' . $select->get() . '</label>';
        $field = '<input class="'.$this->getHTMLClass().'" type="text" name="address_field_url_value[]" value="" />';
        $f->setVar('label', $label, false);
        $f->setVar('field', $field, false);
        $f->setVar('html_id', $this->getHTMLId('url_hidden'), false);
		$urls_hidden_block = '<div id="'.$this->getHTMLId('url_hidden_div').'" class="hidden">'.$f->parse($fragment).'</div>';
		$urls_add_block = '<div>'.$f->parse($fragment).'</div>';
		
        $field = '<a class="bt5" href="javascript:void(0);" onclick="
						inp = $(\'#'.$this->getHTMLId('url_hidden').'\').clone();
						inp.attr({ id: \'\' });
						$(\'#'.$this->getHTMLId('url_hidden_div').'\').before(inp);
						">+ '.pz_i18n::msg('add_urlfield').'</a>';
        $f = new pz_fragment();
        $f->setVar('label', '<label></label>', false);
        $f->setVar('field', $field, false);
        $urls_add_button = $f->parse($fragment);

		$urls_output = '<h2 class="hl2">' . pz_i18n::msg('address_url') . '</h2>';
		$urls_output .= implode("", $url_blocks);
		$urls_output .= $urls_add_block;
		$urls_output .= $urls_hidden_block;
		$urls_output .= $urls_add_button;

		$output .= '<div id="pz_address_fields_url">'.$urls_output.'</div>'.'<div class="split-h"></div>';


        // X-SOCIALPROFILE $socialprofile_value_types

        $f = new pz_fragment();
        $f->setVar('name', $name, false);
        $f->setVar('class', 'socialprofile_field', false);

        $socialprofile_blocks = [];
        foreach ($socialprofiles as $socialprofile) {
            $select = new rex_select();
            $select->setSize(1);
            $select->setStyle('width:80px;');
            $select->setName('address_field_socialprofile_value_type[]');
            foreach ($socialprofile_value_types as $value_type) {
                $select->addOption($value_type, $value_type);
            }

            if (!in_array($socialprofile['value_type'], $socialprofile_value_types)) {
                $select->addOption($socialprofile['value_type'], $socialprofile['value_type']);
            }
            $select->setSelected($socialprofile['value_type']);

            $label = '<label class="'.$this->getHTMLClass().'">' . $select->get() . '</label>';
            $field = '<input class="'.$this->getHTMLClass().'" type="text" name="address_field_socialprofile_value[]" value="'.htmlspecialchars($socialprofile['value']).'" />';
            $f->setVar('label', $label, false);
            $f->setVar('field', $field, false);
            $f->setVar('class', 'socialprofile_field', false);
            $socialprofile_blocks[] = $f->parse($fragment);
        }

        $select = new rex_select();
        $select->setSize(1);
        $select->setStyle('width:80px;');
        $select->setName('address_field_socialprofile_value_type[]');
        foreach ($socialprofile_value_types as $value_type) {
            $select->addOption($value_type, $value_type);
        }
        $label = '<label class="'.$this->getHTMLClass().'" >' . $select->get() . '</label>';
        $field = '<input class="'.$this->getHTMLClass().'" type="text" name="address_field_socialprofile_value[]" value="" />';
        $f->setVar('label', $label, false);
        $f->setVar('field', $field, false);
        $f->setVar('html_id', $this->getHTMLId('socialprofile_hidden'), false);
		$socialprofiles_hidden_block = '<div id="'.$this->getHTMLId('socialprofile_hidden_div').'" class="hidden">'.$f->parse($fragment).'</div>';
		$socialprofiles_add_block = '<div>'.$f->parse($fragment).'</div>';

        $field = '<a class="bt5" href="javascript:void(0);" onclick="
						inp = $(\'#'.$this->getHTMLId('socialprofile_hidden').'\').clone();
						inp.attr({ id: \'\' });
						$(\'#'.$this->getHTMLId('socialprofile_hidden_div').'\').before(inp);
						">+ '.pz_i18n::msg('add_socialprofile').'</a>';
        $f = new pz_fragment();
        $f->setVar('label', '<label></label>', false);
        $f->setVar('field', $field, false);
        $socialprofiles_add_button = $f->parse($fragment);

		$socialprofiles_output = '<h2 class="hl2">' . pz_i18n::msg('address_socialprofile') . '</h2>';
		$socialprofiles_output .= implode("", $socialprofile_blocks);
		$socialprofiles_output .= $socialprofiles_add_block;
		$socialprofiles_output .= $socialprofiles_hidden_block;
		$socialprofiles_output .= $socialprofiles_add_button;

		$output .= '<div id="pz_address_fields_socialprofile">'.$socialprofiles_output.'</div>'.'<div class="split-h"></div>';




        // IMPP $impp_value_types

        $f = new pz_fragment();
        $f->setVar('name', $name, false);
        $f->setVar('class', 'yform1b impp_field', false);

        $impp_blocks = [];
        foreach ($impps as $impp) {
            $lselect = new rex_select();
            $lselect->setSize(1);
            $lselect->setStyle('width:80px;');
            $lselect->setName('address_field_impp_label[]');
            foreach ($impp_labels as $label) {
                $lselect->addOption($label, $label);
            }

            if (!in_array($impp['label'], $impp_labels)) {
                $lselect->addOption($impp['label'], $impp['label']);
            }
            $lselect->setSelected($impp['label']);

            $select = new rex_select();
            $select->setSize(1);
            $select->setStyle('width:80px;');
            $select->setName('address_field_impp_value_type[]');
            foreach ($impp_value_types as $value_type => $v) {
                $select->addOption($value_type, $value_type);
            }

            if (!array_key_exists($impp['value_type'], $impp_value_types)) {
                $select->addOption($impp['value_type'], $impp['value_type']);
            }
            $select->setSelected($impp['value_type']);

            $label = '<label class="'.$this->getHTMLClass('label').'">' . $lselect->get() . '</label>';
            $label .= '<label class="'.$this->getHTMLClass().'">' . $select->get() . '</label>';
            $field = '<input class="'.$this->getHTMLClass().'" type="text" name="address_field_impp_value[]" value="'.htmlspecialchars($impp['exploded_value']).'" />';
            $f->setVar('label', $label, false);
            $f->setVar('field', $field, false);
            $impp_blocks[] = $f->parse($fragment);
        }

        $lselect = new rex_select();
        $lselect->setSize(1);
        $lselect->setStyle('width:80px;');
        $lselect->setName('address_field_impp_label[]');
        foreach ($impp_labels as $label) {
            $lselect->addOption($label, $label);
        }

        $select = new rex_select();
        $select->setSize(1);
        $select->setStyle('width:80px;');
        $select->setName('address_field_impp_value_type[]');
        foreach ($impp_value_types as $value_type => $v) {
            $select->addOption($value_type, $value_type);
        }
        $label = '<label class="'.$this->getHTMLClass('label').'" >' . $lselect->get() . '</label>';
        $label .= '<label class="'.$this->getHTMLClass().'" >' . $select->get() . '</label>';
        $field = '<input class="'.$this->getHTMLClass().'" type="text" name="address_field_impp_value[]" value="" />';
        $f->setVar('label', $label, false);
        $f->setVar('field', $field, false);
        $f->setVar('html_id', $this->getHTMLId('impp_hidden'), false);
        $impps_hidden_block = '<div id="'.$this->getHTMLId('impp_hidden_div').'" class="hidden">'.$f->parse($fragment).'</div>';
		$impps_add_block = '<div>'.$f->parse($fragment).'</div>';

        $field = '<a class="bt5" href="javascript:void(0);" onclick="
						inp = $(\'#'.$this->getHTMLId('impp_hidden').'\').clone();
						inp.attr({ id: \'\' });
						$(\'#'.$this->getHTMLId('impp_hidden_div').'\').before(inp);
						">+ '.pz_i18n::msg('add_impp').'</a>';
        $f = new pz_fragment();
        $f->setVar('label', '<label></label>', false);
        $f->setVar('field', $field, false);
        $impps_add_button = $f->parse($fragment);

		$impps_output = '<h2 class="hl2">' . pz_i18n::msg('address_impp') . '</h2>';
		$impps_output .= implode("", $impp_blocks);
		$impps_output .= $impps_add_block;
		$impps_output .= $impps_hidden_block;
		$impps_output .= $impps_add_button;

		$output .= '<div id="pz_address_fields_impp">'.$impps_output.'</div>'.'<div class="split-h"></div>';




        $this->params['form_output'][$this->getId()] = $output;

        $this->pz_address_fields['TEL'] = $phones;
        $this->pz_address_fields['ADR'] = $postaddresses;
        $this->pz_address_fields['EMAIL'] = $emails;
        $this->pz_address_fields['URL'] = $urls;
        $this->pz_address_fields['X-SOCIALPROFILE'] = $socialprofiles;

        return;
    }

    public function getDescription()
    {
        return 'pz_address_fields -> Beispiel: text|label|Bezeichnung|defaultwert|[no_db]|classes';
    }

    public function postAction()
    {
        // id 	address_id 	type 			label 		preferred 	value_type 	value
        // 2096 	25 		IMPP 			WORK 		1 			Skype 		skype:gregorharlan
        // 2095 	25 		X-SOCIALPROFILE 			0 			twitter 	http://twitter.com/gregorharlan
        // 1858 	33 		TEL 			HOME 		0 			VOICE 		+49-69-48008641
        // 2446 	29 		TEL 			WORK,FAX 	0 	  		 			+49-69-94944262

        $address_id = $this->params['main_id'];

        if ($address_id > 0) {
            foreach ($this->pz_address_fields as $type => $datas) {
                // delete
                $d = rex_sql::factory();
                // $d->debugsql = 1;
                $d->setQuery('delete from pz_address_field where type = ? and address_id = ? ', [$type, $address_id]);

                foreach ($datas as $data) {
                    $d = rex_sql::factory();
                    // $d->debugsql = 1;
                    $d->setTable('pz_address_field');
                    $d->setValue('address_id', $address_id);
                    $d->setValue('type', $type);
                    $d->setValue('label', $data['label']);
                    $d->setValue('preferred', $data['preferred']);
                    $d->setValue('value_type', $data['value_type']);
                    $d->setValue('value', $data['value']);
                    $d->insert();
                }
            }
        }
    }
}