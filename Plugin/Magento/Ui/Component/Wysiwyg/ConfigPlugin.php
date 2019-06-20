<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 */
namespace Magefan\WysiwygAdvanced\Plugin\Magento\Ui\Component\Wysiwyg;

/**
 * Class ConfigPlugin
 * @package Magefan\WysiwygAdvanced\Plugin\Magento\Ui\Component\Wysiwyg
 */
class ConfigPlugin
{

    /**
     * @var \Magento\Ui\Block\Wysiwyg\ActiveEditor
     */
    protected $activeEditor;

    /**
     * ConfigPlugin constructor.
     * @param \Magento\Ui\Block\Wysiwyg\ActiveEditor $activeEditor
     */
    public function __construct(\Magento\Ui\Block\Wysiwyg\ActiveEditor $activeEditor)
    {
        $this->activeEditor = $activeEditor;
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
        // Get current wysiwyg adapter's path
        $editor = $this->activeEditor->getWysiwygAdapterPath();

        // Is the current wysiwyg tinymce v4?
        if(strpos($editor,'tinymce4Adapter')){

	        if (($result->getDataByPath('settings/menubar')) || ($result->getDataByPath('settings/toolbar')) || ($result->getDataByPath('settings/plugins'))){
	            // do not override ui_element config (unsure if this is needed)
	            return $result;
	        }

	        $settings = $result->getData('settings');

	        if (!is_array($settings)) {
	            $settings = [];
	        }

	        // configure tinymce settings 
	        $settings['menubar'] = true;
	        $settings['toolbar'] = 'undo redo | styleselect | fontsizeselect | forecolor backcolor | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | table | image | code';
	        $settings['plugins'] = 'textcolor image code';

	        $result->setData('settings', $settings);
	        return $result;
        } else{ // don't make any changes if the current wysiwyg editor is not tinymce 4
            return $result;
        }
    }
}
