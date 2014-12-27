<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Page\Controller\Front;

use Pi;
use Pi\Filter;
use Pi\Mvc\Controller\ActionController;
use Pi\Db\RowGateway\RowGateway;
use Zend\Mvc\MvcEvent;
use Zend\Db\Sql\Expression;

class IndexController extends ActionController
{
    protected function render($row)
    {
        if (!$row instanceof RowGateway || !$row->active) {
            $title      = __('Page request');
            $content    = __('The page requested does not exist.');
            $markup     = '';
            $url        = '';
        } else {
            $content    = $row->content;
            $markup     = $row->markup ?: 'text';
            if ($content && 'pthml' != $markup) {
                $content = Pi::service('markup')->render(
                    $content, 
                    'html',
                    $markup
                );
            }
            $title = $row->title;
            $url = Pi::url($this->url('page', $row->toArray()));
            // update clicks
            $model = $this->getModel('page');
            $model->increment('clicks', array('id' => $row->id));

            // Module config
            $config = Pi::config('', $this->getModule());
            // Set SEO data
            $seoTitle = $row->seo_title ?: $row->title;
            $seoDescription = $row->seo_description ?: $row->title;
            $seoKeywords = $row->seo_keywords ?: $row->title;
            $filter = new Filter\HeadKeywords;
            if (isset($config['keywords_replace_space'])) {
                $filter->setOptions(array(
                    'force_replace_space' => (bool) $config['keywords_replace_space']
                ));
            }
            $seoKeywords = $filter($seoKeywords);
            // Set view
            $this->view()->headTitle($seoTitle);
            $this->view()->headDescription($seoDescription, 'set');
            $this->view()->headKeywords($seoKeywords, 'set');
            $this->view()->assign('config', $config);
            if ($row->theme) {
                $this->view()->setTheme($row->theme);
            }
            if ($row->layout) {
                $this->view()->setLayout($row->layout);
            }
        }

        if ($row->template) {
            $this->view()->setTemplate($row->template);
        } else {
            $this->view()->setTemplate('page-view');
        }
        
        $this->view()->assign(array(
            'title'     => $title,
            'content'   => $content,
            'markup'    => $markup,
            'url'       => $url,
        ));
    }

    /**
     * Page render
     *
     * @see Module\Page\Route\Page
     */
    public function indexAction()
    {
        $id     = $this->params('id');
        $name   = $this->params('name');
        $slug   = $this->params('slug');

        $row = null;
        if ($id) {
            $row = $this->getModel('page')->find($id);
        } elseif ($name) {
            $row = $this->getModel('page')->find($name, 'name');
        } elseif ($slug) {
            $row = $this->getModel('page')->find($name, 'slug');
        }
        if ($row && $row->active) {
            $nav = Pi::registry('nav', $this->getModule())->read();
            if (isset($nav[$row->id])) {
                $nav[$row->id]['active'] = 1;
            } else {
                $nav = array();
            }
        } else {
            $nav = array();
        }
        $this->view()->assign('nav', $nav);

        $this->render($row);
    }

    /**
     * Transform an "action" token into a method name
     *
     * @param  string $action
     * @return string
     */
    public static function getMethodFromAction($action)
    {
        return 'indexAction';
    }
}
