<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Widget\Controller\Admin;

/**
 * For list group block
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class ListController extends WidgetController
{
    /**
     * {@inheritDoc}
     */
    protected $type = 'list';

    /**
     * {@inheritDoc}
     */
    protected $editTemplate = 'widget-list';

    /**
     * {@inheritDoc}
     */
    protected $formClass = 'BlockListForm';

    /**
     * {@inheritDoc}
     */
    protected function canonizePost(array $values)
    {
        $values['content'] = json_decode($values['content'], true);

        return $values;
    }
}
