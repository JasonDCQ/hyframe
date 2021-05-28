<?php
/**
 * SCRIPT_NAME: FormBuilder.php
 * Created by PhpStorm.
 * Time: 2016/4/13 23:23
 * FUNCTION: 快速表单
 * @author: fudaoji<fdj@kuryun.cn>
 */

namespace App\Controller\Admin;

use App\Controller\AbstractController;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Utils\Context;
use Hyperf\View\RenderInterface;
use App\Middleware\AdminMiddleware;

/**
 * @Middleware(AdminMiddleware::class)
 */
class FormBuilder extends AbstractController
{
    protected $_meta_title;            // 页面标题
    protected $_tab_nav = array();     // 页面Tab导航
    protected $_post_url = '';              // 表单提交地址
    protected $_form_items = array();  // 表单项目
    protected $_extra_items = array(); // 额外已经构造好的表单项目
    protected $_form_data = array();   // 表单数据
    protected $_extra_html;            // 额外功能代码
    protected $_ajax_submit = true;    // 是否ajax提交
    protected $_template = '/admin/builder/form';  // 模版
    protected $_tip = '';              // 提示语

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @Inject()
     * @var RenderInterface
     */
    private $render;

    /**
     * 设置表单顶部的tip
     * @param string $tip
     * @return FormBuilder
     */
    public function setTip($tip = ''){
        $this->_tip = $tip;
        return $this;
    }

    /**
     * 设置页面标题
     * @param string $meta_title  标题文本
     * @return object  $this
     */
    public function setMetaTitle($meta_title) {
        $this->_meta_title = $meta_title;
        return $this;
    }

    /**
     * 设置Tab按钮列表
     * @param $tab_list Tab列表  array(
     *                               'title' => '标题',
     *                               'href' => 'http://www.corethink.cn'
     *                           )
     * @param $current_tab 当前tab
     * @return $this
     */
    public function setTabNav($tab_list, $current_tab) {
        $this->_tab_nav = [
            'tab_list' => $tab_list,
            'current_tab' => $current_tab
        ];
        return $this;
    }

    /**
     * 直接设置表单项数组
     * @param $extra_items 表单项数组
     * @return $this
     */
    public function setExtraItems($extra_items) {
        $this->_extra_items = $extra_items;
        return $this;
    }

    /**
     * 设置表单提交地址
     * @param $post_url 提交地址
     * @return $this
     */
    public function setPostUrl($post_url) {
        $this->_post_url = $post_url;
        return $this;
    }

    /**
     * 加入一个表单项
     * @param $name 表单名
     * @param $type 表单类型(取值参考系统配置FORM_ITEM_TYPE)
     * @param $title 表单标题
     * @param $tip 表单提示说
     * @param array $options 表单options
     * @param string $extra_class 表单项是否隐藏
     * @param string $extra_attr 表单项额外属性
     * @return $this
     */
    public function addFormItem($name, $type, $title, $tip = '', $options = [], $extra_attr = '', $extra_class = '') {
        $item['name'] = $name;
        $item['type'] = $type;
        $item['title'] = $title;
        $item['tip'] = $tip;
        $item['options'] = $options;
        $item['extra_attr'] = $extra_attr;
        $item['extra_class'] = $extra_class;
        $this->_form_items[] = $item;
        return $this;
    }

    /**
     * 设置表单表单数据
     * @param $form_data 表单数据
     * @return $this
     */
    public function setFormData($form_data) {
        $this->_form_data = $form_data;
        return $this;
    }

    /**
     * 设置额外功能代码
     * @param $extra_html 额外功能代码
     * @return $this
     */
    public function setExtraHtml($extra_html) {
        $this->_extra_html = $extra_html;
        return $this;
    }

    /**
     * 设置提交方式
     * @param bool $ajax_submit 标题文本
     * @return $this
     */
    public function setAjaxSubmit($ajax_submit = true) {
        $this->_ajax_submit = $ajax_submit;
        return $this;
    }

    /**
     * 设置页面模版
     * @param string $template 模版
     * @return $this
     */
    public function setTemplate($template) {
        $this->_template = $template;
        return $this;
    }

    /**
     * 显示页面
     * @param array $assign
     * @param string $view
     * @return mixed
     */
    public function show($assign = [], $view = '') {
        //额外已经构造好的表单项目与单个组装的的表单项目进行合并
        $this->_form_items = array_merge($this->_form_items, $this->_extra_items);

        //编译表单值
        if ($this->_form_data) {
            foreach ($this->_form_items as &$item) {
                if (isset($this->_form_data[$item['name']])) {
                    $item['value'] = $this->_form_data[$item['name']];
                }
            }
        }

        $assign = array_merge([
            'module' => Context::get('module'),
            'controller' => Context::get('controller'),
            'action' => Context::get('action'),
            'meta_title' => $this->_meta_title,          // 页面标题
            'tab_nav' => $this->_tab_nav,             // 页面Tab导航
            'post_url' => $this->_post_url,         //提交的url
            'form_items' =>  $this->_form_items,  //表单项目
            'ajax_submit' => $this->_ajax_submit,  //是否ajax提交
            'extra_html' => $this->_extra_html,    // 额外HTML代码
            'tip'   => $this->_tip,
            'system_config' => config('system')
        ], $assign);
        unset($this->_meta_title, $this->_tab_nav,$this->_post_url,$this->_form_items,$this->_ajax_submit,$this->_extra_html, $this->_tip);

        return $this->render->render($this->_template, $assign);
    }
}
