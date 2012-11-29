<?php
/**
 * ConfigResolverService
 *
 * @package RepoVid
 * @author  Peter Smith <peter@orukusaki.co.uk>
 * @link    github.com/orukusaki/RepoVid
 */
namespace RepoVid\Service;
use Symfony\Component\Process\Process;

/**
 * Config Resolver Service
 *
 * @package RepoVid
 * @author  Peter Smith <peter@orukusaki.co.uk>
 * @link    github.com/orukusaki/RepoVid
 */
class ConfigResolverService
{
    /**
     * Resolve Config
     *
     * @param array|object $config Config
     *
     * @api
     *
     * @return mixed
     */
    public function resolve($config)
    {
        return $this->subResolve($config);
    }

    /**
     * Resolve Config Recursively
     *
     * @param array|object $config   Config
     * @param array|object $position Position to start from
     *
     * @return mixed
     */
    protected function subResolve($config, $position = null)
    {
        $search = $position ?: $config;

        if (is_object($search)) {
            $search = clone $search;
        }

        foreach ($search as $key => &$value) {
            if (is_object($value) || is_array($value)) {
                $value = $this->subResolve($config, $value);

            } else {
                $value = $this->substitute($value, $config);
            }
        }
        return $search;
    }

    /**
     * Substitute
     *
     * @param string       $value         Value
     * @param array|object $substitutions Substitutions
     *
     * @return string
     */
    protected function substitute($value, $substitutions)
    {
        static $substituting = array();

        if (!preg_match_all('/\{(.*?)\}/', $value, $matches)) {
            return $value;
        }

        foreach ($matches[1] as $index => $match) {
            if (isset($substituting[$match])) {
                throw new \RuntimeException('Already trying to match ' . $match);
            }

            $parts = explode('/', $match);

            $position = &$substitutions;
            foreach ($parts as $part) {
                if (is_array($position) && array_key_exists($part, $position)) {

                    $position = &$position[$part];

                } elseif (is_object($position) && property_exists($position, $part)) {

                    $position = &$position->$part;
                } else {
                    // Couldn't resolve - abort
                    return $value;
                }
            }

            $substituting[$match] = true;
            $replacement = $this->substitute($position, $substitutions);
            $value = str_replace($matches[0][$index], $replacement, $value);
            unset($substituting[$match]);
        }
        return $value;
    }
}
