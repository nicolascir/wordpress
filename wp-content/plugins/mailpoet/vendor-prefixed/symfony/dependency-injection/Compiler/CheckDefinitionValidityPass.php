<?php
 namespace MailPoetVendor\Symfony\Component\DependencyInjection\Compiler; if (!defined('ABSPATH')) exit; use MailPoetVendor\Symfony\Component\DependencyInjection\ContainerBuilder; use MailPoetVendor\Symfony\Component\DependencyInjection\Exception\EnvParameterException; use MailPoetVendor\Symfony\Component\DependencyInjection\Exception\RuntimeException; use MailPoetVendor\Symfony\Component\DependencyInjection\Loader\FileLoader; class CheckDefinitionValidityPass implements CompilerPassInterface { public function process(ContainerBuilder $container) { foreach ($container->getDefinitions() as $id => $definition) { if ($definition->isSynthetic() && !$definition->isPublic()) { throw new RuntimeException(\sprintf('A synthetic service ("%s") must be public.', $id)); } if (!$definition->isAbstract() && !$definition->isSynthetic() && !$definition->getClass() && (!$definition->getFactory() || !\preg_match(FileLoader::ANONYMOUS_ID_REGEXP, $id))) { if ($definition->getFactory()) { throw new RuntimeException(\sprintf('Please add the class to service "%s" even if it is constructed by a factory since we might need to add method calls based on compile-time checks.', $id)); } if (\class_exists($id) || \interface_exists($id, \false)) { if (\str_starts_with($id, '\\') && 1 < \substr_count($id, '\\')) { throw new RuntimeException(\sprintf('The definition for "%s" has no class attribute, and appears to reference a class or interface. Please specify the class attribute explicitly or remove the leading backslash by renaming the service to "%s" to get rid of this error.', $id, \substr($id, 1))); } throw new RuntimeException(\sprintf('The definition for "%s" has no class attribute, and appears to reference a class or interface in the global namespace. Leaving out the "class" attribute is only allowed for namespaced classes. Please specify the class attribute explicitly to get rid of this error.', $id)); } throw new RuntimeException(\sprintf('The definition for "%s" has no class. If you intend to inject this service dynamically at runtime, please mark it as synthetic=true. If this is an abstract definition solely used by child definitions, please add abstract=true, otherwise specify a class to get rid of this error.', $id)); } foreach ($definition->getTags() as $name => $tags) { foreach ($tags as $attributes) { foreach ($attributes as $attribute => $value) { if (!\is_scalar($value) && null !== $value) { throw new RuntimeException(\sprintf('A "tags" attribute must be of a scalar-type for service "%s", tag "%s", attribute "%s".', $id, $name, $attribute)); } } } } if ($definition->isPublic() && !$definition->isPrivate()) { $resolvedId = $container->resolveEnvPlaceholders($id, null, $usedEnvs); if (null !== $usedEnvs) { throw new EnvParameterException([$resolvedId], null, 'A service name ("%s") cannot contain dynamic values.'); } } } foreach ($container->getAliases() as $id => $alias) { if ($alias->isPublic() && !$alias->isPrivate()) { $resolvedId = $container->resolveEnvPlaceholders($id, null, $usedEnvs); if (null !== $usedEnvs) { throw new EnvParameterException([$resolvedId], null, 'An alias name ("%s") cannot contain dynamic values.'); } } } } } 