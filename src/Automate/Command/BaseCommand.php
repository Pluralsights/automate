<?php

/*
 * This file is part of the Automate package.
 *
 * (c) Julien Jacottet <jjacottet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Automate\Command;

use Automate\Command\Helper\DialogHelper;
use Automate\Context\Context;
use Automate\Context\ContextAwareInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Base automate command
 *
 * @author Julien Jacottet <jjacottet@gmail.com>
 *
 */
class BaseCommand extends Command implements ContextAwareInterface
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var array
     */
    private $configuration;

    /**
     * {@inheritDoc}
     */
    public function setContext(Context $context = null)
    {
        $this->context = $context;
    }

    /**
     * {@inheritDoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->context->setInput($input);
        $this->context->setOutput($output);

        $dialog = $this->getHelperSet()->get('dialog');
        if (!$dialog || get_class($dialog) !== 'Automate\Command\Helper\DialogHelper') {
            $this->getHelperSet()->set($dialog = new DialogHelper());
        }
    }

    /**
     * @return DialogHelper
     */
    protected function getDialogHelper()
    {
        return $this->getHelperSet()->get('dialog');
    }

    /**
     * @return null|TreeBuilder
     */
    public function getConfigurationBuilder()
    {
        return null;
    }

    /**
     * Get configuration
     *
     * @return array
     */
    public function getConfiguration()
    {
        if ($this->configuration) {
            return $this->configuration;
        }

        if (!$this->getConfigurationBuilder() instanceof TreeBuilder) {
            return array();
        }

        $processor = new Processor();
        $this->configuration = $processor->process($this->getConfigurationBuilder()->buildTree(), array($this->context->getConfig()));

        return $this->configuration;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    protected function getRemotesConfigurationBuilder()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('remotes');

        $node
            ->isRequired()
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->children()
                    ->scalarNode('host')->isRequired()->end()
                    ->scalarNode('user')->isRequired()->end()
                    ->scalarNode('password')->defaultNull()->end()
                    ->scalarNode('rsa')->defaultNull()->end()
                    ->arrayNode('groups')
                        ->defaultValue(array('web'))
                        ->beforeNormalization()
                            ->ifString()->then(function ($value) { return array($value); })
                        ->end()
                        ->prototype('scalar')->end()
                    ->end()
                    ->booleanNode('master')->defaultFalse()->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    protected function getHookConfigurationBuilder($name)
    {
        $builder = new TreeBuilder();
        $node = $builder->root($name);

        $node
            ->prototype('array')
                ->children()
                    ->scalarNode('name')->defaultNull()->end()
                    ->variableNode('params')->defaultValue(array())->end()
                ->end()
            ->end()
        ;

        return $node;
    }

}
