<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         Form
 */

namespace Pi\Form\View\Helper;

use Laminas\Form\ElementInterface;
use Laminas\Form\FieldsetInterface;

//use Laminas\Form\View\Helper\AbstractHelper;

/**
 * Fieldset helper
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 *
 * ToDo : fix for zend version 2.4.9
 */
class FormFieldset extends AbstractHelper
{
    /** @var FormRow Row render helper */
    protected $rowHelper;

    /**
     * If set to true, collections are automatically wrapped around a fieldset
     *
     * @var bool
     */
    protected $shouldWrap = true;

    /**
     * If set to true, collections are automatically wrapped around a fieldset
     *
     * @param bool $wrap
     * @return self
     */
    public function setShouldWrap($wrap)
    {
        $this->shouldWrap = (bool)$wrap;

        return $this;
    }

    /**
     * Get wrapped
     *
     * @return bool
     */
    public function shouldWrap()
    {
        return $this->shouldWrap();
    }

    /**
     * Retrieve the FormRow helper
     *
     * @return FormRow
     */
    protected function getRowHelper()
    {
        if ($this->rowHelper) {
            return $this->rowHelper;
        }

        if (method_exists($this->view, 'plugin')) {
            $this->rowHelper = $this->view->plugin('form_row');
        }

        return $this->rowHelper;
    }

    /**
     * {@inheritDoc}
     */
    public function render(ElementInterface $element, $options = [])
    {
        $renderer = $this->getView();
        if (!method_exists($renderer, 'plugin')) {
            // Bail early if renderer is not pluggable
            return '';
        }
        $wrap = null;
        if (is_bool($options)) {
            $wrap = $options;
        } elseif (is_array($options) && isset($options['wrap'])) {
            $wrap = (bool)$options['wrap'];
        }
        if (null !== $wrap) {
            $this->setShouldWrap($wrap);
        }

        $markup           = '';
        $escapeHtmlHelper = $this->getEscapeHtmlHelper();
        $rowHelper        = $this->getRowHelper();

        foreach ($element->getIterator() as $elementOrFieldset) {
            if ($elementOrFieldset instanceof FieldsetInterface) {
                $markup .= $this->render($elementOrFieldset);
            } elseif ($elementOrFieldset instanceof ElementInterface) {
                $markup .= $rowHelper($elementOrFieldset);
            }
        }

        // Every collection is wrapped by a fieldset if needed
        if ($this->shouldWrap) {
            $label = $element->getLabel();

            if (!empty($label)) {
                $label = $escapeHtmlHelper($label);

                $markup = sprintf(
                    '<fieldset><legend>%s</legend>%s</fieldset>',
                    $label,
                    $markup
                );
            }
        }

        return $markup;
    }
}
