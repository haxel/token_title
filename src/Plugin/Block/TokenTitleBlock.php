<?php

namespace Drupal\token_title\Plugin\Block;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Block\Plugin\Block\PageTitleBlock;

/**
 * Provides a block to display the page title with token configuration.
 *
 * @Block(
 *   id = "token_title_block",
 *   admin_label = @Translation("Token title")
 * )
 */

class TokenTitleBlock extends PageTitleBlock implements ContainerFactoryPluginInterface {

    /**
     * Stores the configuration factory.
     *
     * @var \Drupal\Core\Config\ConfigFactoryInterface
     */
    protected $configFactory;

    /**
     * Creates a TokenTitleBlock instance.
     *
     * @param array $configuration
     *   A configuration array containing information about the plugin instance.
     * @param string $plugin_id
     *   The plugin_id for the plugin instance.
     * @param mixed $plugin_definition
     *   The plugin implementation definition.
     * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
     *   The factory for configuration objects.
     */
    public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory) {
        parent::__construct($configuration, $plugin_id, $plugin_definition);
        $this->configFactory = $config_factory;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
        return new static(
            $configuration,
            $plugin_id,
            $plugin_definition,
            $container->get('config.factory')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function defaultConfiguration() {
        $config = parent::defaultConfiguration();
        return $config + [
            'title_token' => '[node:title]',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function blockForm($form, FormStateInterface $form_state) {

        $form['block_title'] = array(
            '#type' => 'fieldset',
            '#title' => $this->t('token'),
            '#description' => $this->t('Choose which tokens should be used to replace the title.'),
        );

        $form['block_title']['title_token'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('the token to use in the title of the page'),
            '#default_value' => $this->configuration['title_token'] ,
        );
        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function blockSubmit($form, FormStateInterface $form_state) {
        $block_title = $form_state->getValue('block_title');
        $this->configuration['title_token'] = $block_title['title_token'];
    }

    /**
     * {@inheritdoc}
     */

    public function build() {

        $title = $this->title;

        if($node = \Drupal::routeMatch()->getParameter('node')){
            $title = \Drupal::token()->replace($this->configuration['title_token'], array('node'=>$node) );
        }

        return [
            '#type' => 'page_title',
            '#title' => $title,
        ];
    }


}
