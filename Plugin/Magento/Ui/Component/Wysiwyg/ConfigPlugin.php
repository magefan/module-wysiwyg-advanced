<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 */
namespace Magefan\WysiwygAdvanced\Plugin\Magento\Ui\Component\Wysiwyg;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Config Plugin
 */
class ConfigPlugin
{

    /**
     * @var \Magento\Ui\Block\Wysiwyg\ActiveEditor
     */
    protected $activeEditor;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * ConfigPlugin constructor.
     * @param null $activeEditor
     */
    public function __construct(
        $activeEditor = null,
        RequestInterface $request = null,
        ScopeConfigInterface $scopeConfig = null
    ) {
        try {
            /* Fix for Magento 2.1.x & 2.2.x that does not have this class and plugin should not work there */
            if (class_exists(\Magento\Ui\Block\Wysiwyg\ActiveEditor::class)) {
                $this->activeEditor = $activeEditor
                    ?: ObjectManager::getInstance()->get(\Magento\Ui\Block\Wysiwyg\ActiveEditor::class);
            }
        } catch (\Exception $e) {

        }

        $this->request = $request ?: ObjectManager::getInstance()->get(\Magento\Framework\App\RequestInterface::class);
        $this->scopeConfig = $scopeConfig ?: ObjectManager::getInstance()->get(\Magento\Framework\App\Config\ScopeConfigInterface::class);

    }

    /**
     * Enable variables & widgets on product edit page
     *
     * @param \Magento\Ui\Component\Wysiwyg\ConfigInterface $configInterface
     * @param array $data
     * @return array
     */
    public function beforeGetConfig(
        \Magento\Ui\Component\Wysiwyg\ConfigInterface $configInterface,
        $data = []
    ) {
        if (!$this->activeEditor) {
            return [$data];
        }

        $data['add_variables'] = true;
        $data['add_widgets'] = true;

        return [$data];
    }

    /**
     * Return WYSIWYG configuration
     *
     * @param \Magento\Ui\Component\Wysiwyg\ConfigInterface $configInterface
     * @param \Magento\Framework\DataObject $result
     * @return \Magento\Framework\DataObject
     */
    public function afterGetConfig(
        \Magento\Ui\Component\Wysiwyg\ConfigInterface $configInterface,
        \Magento\Framework\DataObject $result
    ) {
        if (!$this->activeEditor) {
            return $result;
        }

        // Get current wysiwyg adapter's path
        $editor = $this->activeEditor->getWysiwygAdapterPath();

        // Is the current wysiwyg tinymce v4 or v5?
        if (strpos($editor, 'tinymce4Adapter') || strpos($editor, 'tinymce5Adapter')) {

            if (($result->getDataByPath('settings/menubar')) || ($result->getDataByPath('settings/toolbar')) || ($result->getDataByPath('settings/plugins'))) {
                // do not override ui_element config (unsure if this is needed)
                return $result;
            }

            $settings = $result->getData('settings');

            if (!is_array($settings)) {
                $settings = [];
            }

            // configure tinymce settings
            $settings['menubar'] = true;
            $settings['image_advtab'] = true;

            $settings['plugins'] = 'advlist autolink code colorpicker directionality hr imagetools link media noneditable paste print table textcolor toc visualchars anchor charmap codesample contextmenu help image insertdatetime lists nonbreaking pagebreak preview searchreplace template textpattern visualblocks wordcount magentovariable magentowidget';

            $settings['toolbar1'] = 'magentovariable magentowidget | formatselect | styleselect | fontselect | fontsizeselect | lineheight | forecolor backcolor | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent';
            $settings['toolbar2'] = ' undo redo  | link anchor table charmap | image media insertdatetime | widget | searchreplace visualblocks  help | hr pagebreak';
            $settings['force_p_newlines'] = false;

            $settings['valid_children'] = '+body[style]';

            $result->setData('settings', $settings);

            $type = false;
            if (in_array($this->request->getModuleName(), ['cms', 'catalog'])) {
                $type = $this->request->getModuleName() . '_' . $this->request->getControllerName();
            } elseif ('blog' == $this->request->getModuleName()) {
                $type = $this->request->getModuleName();
            } elseif ('mfproducttabs' == $this->request->getModuleName()) {
                $type = $this->request->getModuleName();
            }

            if ($this->isEnabledOverided($type)) {
                $result->setData('enabled', $this->isEnabled($type));
                $result->setData('hidden', $this->isHidden($type));
            }

            return $result;
        } else { // don't make any changes if the current wysiwyg editor is not tinymce 4
            return $result;
        }
    }

    /**
     * Check whether Wysiwyg enabled option is overided for the page type
     *
     * @param string $type
     * @return bool
     */
    private function isEnabledOverided($type)
    {
        $wysiwygState = $this->scopeConfig->getValue(
            'mfwysiwygadvanced/general/' . $type . '_enabled',
            ScopeInterface::SCOPE_STORE
        );
        return $wysiwygState;
    }


    /**
     * Check whether Wysiwyg is enabled or not
     *
     * @param string $type
     * @return bool
     */
    private function isEnabled($type)
    {
        $wysiwygState = $this->scopeConfig->getValue(
            'mfwysiwygadvanced/general/' . $type . '_enabled',
            ScopeInterface::SCOPE_STORE
        );
        return in_array($wysiwygState, [\Magento\Cms\Model\Wysiwyg\Config::WYSIWYG_ENABLED, \Magento\Cms\Model\Wysiwyg\Config::WYSIWYG_HIDDEN]);
    }

    /**
     * Check whether Wysiwyg is loaded on demand or not
     *
     * @param string $type
     * @return bool
     */
    private function isHidden($type)
    {
        $status = $this->scopeConfig->getValue(
            'mfwysiwygadvanced/general/' . $type . '_enabled',
            ScopeInterface::SCOPE_STORE
        );
        return $status == \Magento\Cms\Model\Wysiwyg\Config::WYSIWYG_HIDDEN;
    }
}
