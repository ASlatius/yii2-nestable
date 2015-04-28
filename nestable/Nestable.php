<?php

/**
 * @copyright Copyright &copy; Arno Slatius 2015
 * @package yii2-nestable
 * @version 1.0
 */

namespace slatiusa\nestable;

use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use gilek\gtreetable\models\TreeModel;

/**
 * Create nestable lists using drag & drop for Yii 2.0.
 * Based on jquery.nestable.js plugin.
 *
 * @author Arno Slatius <a.slatius@gmail.com>
 * @since 1.0
 */
class Nestable extends \kartik\base\Widget
{
	const TYPE_LIST = 'list';
	const TYPE_WITH_HANDLE = 'list-handle';

	/**
	 * @var string the type of the sortable widget
	 * Defaults to Nestable::TYPE_WITH_HANDLE
	 */
	public $type = self::TYPE_WITH_HANDLE;

	/**
	 * @var string, the handle label, this is not HTML encoded
	 */
	public $handleLabel = '<div class="dd-handle dd3-handle">&nbsp;</div>';

	/**
	 * @var array the HTML attributes to be applied to list.
	 * This will be overridden by the [[options]] property within [[$items]].
	 */
	public $listOptions = [];

	/**
	 * @var array the HTML attributes to be applied to all items.
	 * This will be overridden by the [[options]] property within [[$items]].
	 */
	public $itemOptions = [];

	/**
	 * @var array the sortable items configuration for rendering elements within the sortable
	 * list / grid. You can set the following properties:
     * - id: integer, the id of the item. This will get returned on change
	 * - content: string, the list item content (this is not HTML encoded)
	 * - disabled: bool, whether the list item is disabled
     * - options: array, the HTML attributes for the list item.
	 * - contentOptions: array, the HTML attributes for the content
     * - children: array, with item children
	 */
	public $items = [];

    /**
    * @var string the URL to send the callback to. Defaults to current controller / actionNodeMove which
    * can be provided by \slatiusa\nestable\nestableNodeMoveAction by registering that as an action in the
    * controller rendering the Widget.
    * ```
    * public function actions() {
    *    return [
    *        'nodeMove' => [
    *            'class' => 'slatiusa\nestable\NestableNodeMoveAction',
    *        ],
    *    ];
    * }
    * ```
    * Defaults to [current controller/nodeMove] if not set.
    */
    public $url;

    /**
    * @var ActiveQuery that holds the data for the tree to show.
    */
    public $query;

    /**
    * @var array options to be used with the model on list preparation. Supporten properties:
    * - name: {string|function}, attribute name for the item title or unnamed function($model) that returns a
    *         string for each item.
    */
    public $modelOptions = [];

	/**
	 * Initializes the widget
	 */
	public function init() {
        if (null != $this->url) {
            $this->pluginOptions['url'] = $this->url;
        } else {
            $this->pluginOptions['url'] = Url::to([$this->view->context->id.'/nodeMove']);
        }

		parent::init();
		$this->registerAssets();

        Html::addCssClass($this->options, 'dd');
		echo Html::beginTag('div', $this->options);

        if (null != $this->query) {
            $this->items = $this->prepareItems($this->query);
        }
		if (count($this->items) === 0) {
			echo Html::tag('div', '', ['class' => 'dd-empty']);
		}
	}

	/**
	 * Runs the widget
	 *
	 * @return string|void
	 */
	public function run() {
		if (count($this->items) > 0) {
			echo Html::beginTag('ol', ['class' => 'dd-list']);
			echo $this->renderItems();
			echo Html::endTag('ol');
		}
		echo Html::endTag('div');
	}

	/**
	 * Render the list items for the sortable widget
	 *
	 * @return string
	 */
	protected function renderItems($_items = NULL) {
		$_items = is_null($_items) ? $this->items : $_items;
		$items = '';
        $dataid = 0;
		foreach ($_items as $item) {
			$options = ArrayHelper::getValue($item, 'options', ['class' => 'dd-item dd3-item']);
            $options = ArrayHelper::merge($this->itemOptions, $options);
            $dataId  = ArrayHelper::getValue($item, 'id', $dataid++);
            $options = ArrayHelper::merge($options, ['data-id' => $dataId]);

            $contentOptions = ArrayHelper::getValue($item, 'contentOptions', ['class' => 'dd3-content']);
			$content = $this->handleLabel;
			$content .= Html::tag('div', ArrayHelper::getValue($item, 'content', ''), $contentOptions);

			$children = ArrayHelper::getValue($item, 'children', []);
			if (!empty($children)) {
					// recursive rendering children items
				$content .= Html::beginTag('ol', ['class' => 'dd-list']);
				$content .= $this->renderItems($children);
				$content .= Html::endTag('ol');
			}

			$items .= Html::tag('li', $content, $options) . PHP_EOL;
		}
		return $items;
	}

	/**
	 * Register client assets
	 */
	public function registerAssets() {
		$view = $this->getView();
		NestableAsset::register($view);
		$this->registerPlugin('nestable');
		$id = '$("#' . $this->options['id'] . '")';
	}

	/**
	 * @param $partial
	 * @param $arguments
	 */
	public function renderContent($partial, $arguments) {
		return $this->render($partial, $arguments);
	}

    /**
    * put your comment there...
    *
    * @param $activeQuery \yii\db\ActiveQuery
    * @return array
    */
    private function prepareItems($activeQuery)
    {
        $items = [];
        foreach ($activeQuery->all() as $model) {
            $name = ArrayHelper::getValue($this->modelOptions, 'name', 'name');
            $items[] = [
                'id'       => $model->getPrimaryKey(),
                'content'  => (is_callable($name) ? call_user_func($name, $model) : $model->{$name}),
                'children' => $this->prepareItems($model->children(1)),
            ];
        }
        return $items;
    }
}