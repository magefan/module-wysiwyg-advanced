<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 */
namespace Magefan\WysiwygAdvanced\Plugin\Magento\Ui\Component\Wysiwyg;

use Magento\Framework\App\ObjectManager;

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
     * ConfigPlugin constructor.
     * @param null $activeEditor
     */
    public function __construct(
        $activeEditor = null
    ) {
        try {
            /* Fix for Magento 2.1.x & 2.2.x that does not have this class and plugin should not work there */
            if (class_exists(\Magento\Ui\Block\Wysiwyg\ActiveEditor::class)) {
                $this->activeEditor = $activeEditor
                    ?: ObjectManager::getInstance()->get(\Magento\Ui\Block\Wysiwyg\ActiveEditor::class);
            }
        } catch (\Exception $e) {

        }
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

        // Is the current wysiwyg tinymce v4?
        if (strpos($editor, 'tinymce4Adapter')) {

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

            $settings['toolbar1'] = 'magentovariable magentowidget | formatselect | styleselect | fontsizeselect | forecolor backcolor | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent';
            $settings['toolbar2'] = ' undo redo  | link anchor table charmap | image media insertdatetime | widget | searchreplace visualblocks  help | hr pagebreak';
            $settings['force_p_newlines'] = false;

            $settings['valid_children'] = '+body[style]';

            $result->setData('settings', $settings);
            return $result;
        } else { // don't make any changes if the current wysiwyg editor is not tinymce 4
            return $result;
        }
    }
}
