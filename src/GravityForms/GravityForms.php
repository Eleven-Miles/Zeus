<?php

namespace NanoSoup\Zeus\GravityForms;

use GFAPI;
use Timber\User;
use NanoSoup\Zeus\ModuleConfig;

/**
 * Class GravityForms
 * @package Zeus\Wordpress
 */
class GravityForms
{
    /**
     * GravityForms constructor.
     */
    public function __construct($moduleConfig)
    {
        $config = new ModuleConfig($moduleConfig);

        if ($config->getOption('disabled')) {
            return;
        }

        if ($config->getOption('enableStyling')) {
            add_filter('pre_option_rg_gforms_disable_css', '__return_true');
            add_filter('gform_pre_render', [$this, 'amendFormFields']);
            add_filter('gform_submit_button', [$this, 'changeSubmitButton'], 10, 2);
        }

        if ($config->getOption('pushToGTM')) {
            add_filter('gform_confirmation', [$this, 'pushSubmissionGtm'], 10, 4);
        }

        if ($config->getOption('populateCustomFields')) {
            add_filter('gform_field_value_name', [$this, 'populateName']);
            add_filter('gform_field_value_email', [$this, 'populateEmail']);
            add_filter('gform_field_value_phone', [$this, 'populatePhone']);
            add_filter('gform_field_value_company', [$this, 'populateCompany']);
        }

        add_filter('gform_entry_field_value', [$this, 'fieldDisplaySerialisedData'], 10, 4);
        add_filter('gform_export_field_value', [$this, 'exportDisplaySerialisedData'], 10, 4);
    }

    /**
     * @return array
     */
    public static function listGravityForms()
    {
        $forms = GFAPI::get_forms();
        $results = [];
        foreach ($forms as $form) {
            $results[$form['id']] = $form['title'];
        }

        return $results;
    }

    /**
     * Amend form fields for all forms
     *
     * @param $form
     * @return mixed
     */
    public function amendFormFields($form)
    {
        foreach ($form['fields'] as &$field) {
            $field->size = $field->size ?? 'large';
            $field['cssClass'] .= " {$field->type}_wrapper";
            $field['cssClass'] .= " size_{$field->size}";
        }

        return $form;
    }

    /**
     * Amend submit button to be a button rather than input
     *
     * @param $button
     * @param $form
     * @return mixed
     */
    public function changeSubmitButton($button, $form)
    {
        $button = str_replace('input', 'button', $button);
        $button = str_replace('/', '', $button);
        $button .= "{$form['button']['text']}</button>";

        return $button;
    }

    /**
     * Pushes submissions from gravity forms to the data layer
     *
     * @param $confirmation
     * @param $form
     * @param $entry
     * @param $ajax
     * @return string
     */
    public function pushSubmissionGtm($confirmation, $form, $entry, $ajax)
    {
        $confirmation .=
            "<script>" .
            "    window.dataLayer = window.dataLayer || [];" .
            "    window.dataLayer.push({" .
            "        'event': 'gravityFormSubmitted'," .
            "        'formId': '" . $form['id'] . "'" .
            "    });" .
            "</script>";

        return $confirmation;
    }

    /**
     * @param $id
     * @param $data
     * @return array|\WP_Error
     */
    public static function submitForm($id, $data)
    {
        if ($form = GFAPI::get_form($id)) {
            $form_values = $data;

            $input_fields = [];
            foreach ($form['fields'] as $field) {
                $label = str_replace(' ', '_', strtolower($field->label));
                if (isset($form_values[$label])) {
                    $input_fields['input_' . $field['id']] = $form_values[$label];
                }
            }

            return GFAPI::submit_form($id, $input_fields);
        }
    }

    /**
     * @param $key
     * @param $value
     * @return string
     */
    public function outputFieldData($key, $value)
    {
        if (is_numeric($key)) {
            $label = 'Item #' . $key;
        } else {
            $label = preg_replace('/(?<=\\w)(?=[A-Z]|[0-9])/', " $1", $key);
            $label = ucwords($label);
        }

        return '<div><strong>' . $label . '</strong>: ' . $value . '</div>';
    }

    /**
     * @param $values
     * @param $disallowFields
     * @return mixed
     */
    public function formatSerialisedData($values, $disallowFields = [])
    {
        $values = unserialize(html_entity_decode($values));
        $output = '';

        if (is_array($values)) {
            foreach ($values as $key => $value) {
                if (is_array($value)) {
                    $output .= '<div style="margin-bottom: 20px;">';
                    foreach ($value as $key2 => $value2) {
                        if (!is_array($value2) && !is_object($value2) && !in_array($key2, $disallowFields)) {
                            $output .= $this->outputFieldData($key2, $value2);
                        }
                    }
                    $output .= '</div>';
                } else if (!is_object($value) && !in_array($key, $disallowFields)) {
                    $output .= '<div style="margin-bottom: 20px;">';
                    $output .= $this->outputFieldData($key, $value);
                    $output .= '</div>';
                }
            }
        }

        return $output;
    }

    /**
     * Make sure any serialised fields are serialised before being displayed
     *
     * @param $value
     * @param $field
     * @param $lead
     * @param $form
     * @return false|string
     */
    public function fieldDisplaySerialisedData($value, $field, $lead, $form)
    {
        if (is_serialized($value)) {
            $value = $this->formatSerialisedData($value);
        }

        return $value;
    }

    /**
     * Make sure any serialised fields are serialised before being displayed
     * when exporting
     *
     * @param $value
     * @param $form_id
     * @param $field_id
     * @param $entry
     * @return string
     */
    public function exportDisplaySerialisedData($value, $form_id, $field_id, $entry)
    {
        if (is_serialized($value)) {
            $value = $this->formatSerialisedData($value);
        }

        return $value;
    }

    /**
     * @param $value
     * @return string
     */
    public function populateName($value)
    {
        if ($user = wp_get_current_user()) {
            $value = $user->first_name . ' ' . $user->last_name;
        }

        return $value;
    }

    /**
     * @param $value
     * @return string
     */
    public function populateEmail($value)
    {
        if ($user = wp_get_current_user()) {
            $value = $user->user_email;
        }

        return $value;
    }

    /**
     * @param $value
     * @return string
     */
    public function populatePhone($value)
    {
        if ($userId = get_current_user_id()) {
            $value = get_user_meta($userId, 'phone_number', true);
        }

        return $value;
    }

    /**
     * @param $value
     * @return string
     */
    public function populateCompany($value)
    {
        if ($userId = get_current_user_id()) {
            $value = get_user_meta($userId, 'company', true);
        }

        return $value;
    }
}
