<?php

declare(strict_types=1);

namespace PgFramework\Twig;

use DateTime;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;

class FormExtension extends AbstractExtension
{
    /**
   * @return array
   */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('field', [$this, 'field'], [
                'is_safe' => ['html'],
                'needs_context' => true
            ])
        ];
    }

    /**
     * @param array $context
     * @param string $key
     * @param mixed $value
     * @param string|null $label
     * @param array $options
     * @return string
     */
    public function field(array $context, string $key, mixed $value, ?string $label = null, array $options = []): string
    {
        $type = $options['type'] ?? 'text';
        $error = $this->getErrorHTML($context, $key);
        $value = $this->convertValue($value);
        $attributes = [
            'class' => 'form-control' . (isset($options['class']) ? ' ' . $options['class'] : ''),
            'name'  => $key,
            'id'    => $key,
        ];
        $div = "<div class=\"form-group\">";
        $label = "<label for=\"$key\">$label</label>";
        if (isset($options['attributes'])) {
            foreach ($options['attributes'] as $attribute => $val) {
                $attributes[$attribute] = $val;
            }
        }
        if ($error) {
            $attributes['class'] .= ' is-invalid';
        } elseif (isset($context['submitted']) && $context['submitted']) {
            $attributes['class'] .= ' is-valid';
        }
        if ($type === 'textarea') {
            $input = $this->textarea($value, $attributes);
        } elseif ($type === 'file') {
            $input = $this->file($attributes);
        } elseif ($type === 'checkbox') {
            $input = $this->checkbox($value, $attributes);
            $div = "<div class=\"form-check\">";
            $label1 = $label;
            $label = $input;
            $input = $label1;
        } elseif (array_key_exists('options', $options)) {
            $input = $this->select($value, $options['options'], $attributes);
        } else {
            $attributes['type'] = $options['type'] ?? 'text';
            $input = $this->input($value, $attributes);
        }
        return "
            $div
              $label
              $input
              $error
            </div>";
    }

    /**
     * @param [type] $value
     * @return string
     */
    private function convertValue($value): string
    {
        if ($value instanceof DateTime) {
            return $value->format('Y-m-d H:i:s');
        }
        return (string)$value;
    }

    /**
     * @param array $context
     * @param string $key
     * @return string
     */
    private function getErrorHTML(array $context, string $key): string
    {
        $error = $context['errors'][$key] ?? false;
        if ($error) {
            return "<div class=\"invalid-feedback\">$error</div>";
        }
        return "";
    }

    /**
     * @param string|null $value
     * @param array $attributes
     * @return string
     */
    private function textarea(?string $value, array $attributes): string
    {
        return "<textarea " .
            $this->getHtmlFromArray($attributes) .
            ">$value</textarea>";
    }

    /**
     * @param string|null $value
     * @param array $options
     * @param array $attributes
     * @return string
     */
    public function select(?string $value, array $options, array $attributes): string
    {
        $htmlOptions = array_reduce(
            array_keys($options),
            function (string $html, string $key) use ($options, $value) {
                $params = ['value' => $key, 'selected' => $key === $value];
                return $html .
                "<option " . $this->getHtmlFromArray($params) . ">$options[$key]</option>";
            },
            ""
        );
        return "<select " .
        $this->getHtmlFromArray($attributes) .
        ">$htmlOptions</select>";
    }

    /**
     * @param string|null $value
     * @param array $attributes
     * @return string
     */
    private function input(?string $value, array $attributes): string
    {
        return "<input " . $this->getHtmlFromArray($attributes) . " value=\"$value\"/>";
    }

    /**
     * @param string|null $value
     * @param array $attributes
     * @return string
     */
    private function checkbox(?string $value, array $attributes): string
    {
        $attributes['class'] = str_replace('form-control', 'form-check-input', $attributes['class']);
        $html = "<input type=\"hidden\"" .
        " name=\"" . $attributes['name'] . "\"" .
        " value=\"0\"/>";
        if ($value) {
            $attributes['checked'] = true;
        }
        return $html .
            "<input type=\"checkbox\" " .
            $this->getHtmlFromArray($attributes) .
            " value=\"1\"/>";
    }

    /**
     * @param array $attributes
     * @return string
     */
    public function file(array $attributes): string
    {
        return "<input type=\"file\"" .
            $this->getHtmlFromArray($attributes) .
            " />";
    }

    /**
     * @param array $attributes
     * @return string
     */
    private function getHtmlFromArray(array $attributes): string
    {
        $htmlParts = [];
        foreach ($attributes as $key => $value) {
            if ($value === true) {
                $htmlParts[] = "$key";
            } elseif ($value !== false) {
                $htmlParts[] = "$key=\"$value\"";
            }
        }

        return implode(' ', $htmlParts);
    }
}
