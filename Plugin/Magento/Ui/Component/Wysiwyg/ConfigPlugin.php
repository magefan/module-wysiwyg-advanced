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
            $settings['image_advtab'] = true;

            $settings['plugins'] = 'advlist autolink code colorpicker directionality hr imagetools link media noneditable paste print table textcolor toc visualchars anchor charmap codesample contextmenu fullpage help image insertdatetime lists nonbreaking pagebreak preview searchreplace template textpattern visualblocks wordcount magentovariable magentowidget';

            $settings['toolbar1'] = 'magentovariable magentowidget | formatselect | styleselect | fontsizeselect | forecolor backcolor | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent';
            $settings['toolbar2'] = ' undo redo  | link anchor table charmap | image media insertdatetime | widget | searchreplace visualblocks  help | hr pagebreak';

            $result->setData('settings', $settings);
            return $result;
        } else{ // don't make any changes if the current wysiwyg editor is not tinymce 4
            return $result;
        }
    }
}
