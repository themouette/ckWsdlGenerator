<?php
/**
 * This file is part of the ckWebServicePlugin
 *
 * @package   ckWsdlGenerator
 * @author    Christian Kerl <christian-kerl@web.de>
 * @copyright Copyright (c) 2008, Christian Kerl
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @version   SVN: $Id$
 */

/**
 * Enter description here...
 *
 * @package    ckWsdlGenerator
 * @author     Christian Kerl <christian-kerl@web.de>
 */
class ckWsdlGenerator
{
  protected $name;
  protected $namespace;
  protected $endpoint;
  protected $methods = array();

  public function getName()
  {
    return $this->name;
  }

  public function getNamespace()
  {
    return $this->namespace;
  }

  public function getEndpoint()
  {
    return $this->endpoint;
  }

  public function __construct($name, $namespace, $endpoint)
  {
    $this->name = $name;
    $this->namespace = ckXsdNamespace::set('tns', $this->normalizeNamespaceUri($namespace));
    $this->endpoint = $endpoint;
  }

  public function addMethod(ReflectionMethod $method)
  {
    $this->methods[] = $method;
  }

  public function save($file = null)
  {
    $portType = new ckWsdlPortType();
    $portType->setName($this->getName().'PortType');

    foreach($this->methods as $method)
    {
      $portType->addOperation(ckWsdlOperation::create($method->getName(), $method));
    }

    $binding = new ckWsdlSoapBindingDecorator();
    $binding->setName($this->getName().'Binding');
    $binding->setPortType($portType);

    $port = new ckWsdlSoapPortDecorator();
    $port->setName($this->getName().'Port');
    $port->setLocation($this->getEndpoint());
    $port->setBinding($binding);

    $service = new ckWsdlService();
    $service->setName($this->getName().'Service');
    $service->addPort($port);

    $def = new ckWsdlDefinitions();
    $def->setName($this->getName());
    $def->addPortType($portType);
    $def->addBinding($binding);
    $def->addService($service);

    $doc = new DOMDocument();
    $doc->formatOutput = true;
    $doc->appendChild($def->serialize($doc));

    $content = $doc->saveXML();

    if(!is_null($file))
    {
      file_put_contents($file, $content);
    }

    return $content;
  }

  private function normalizeNamespaceUri($namespace)
  {
    return !ckString::endsWith($namespace, '/') ? $namespace.'/' : $namespace;
  }
}