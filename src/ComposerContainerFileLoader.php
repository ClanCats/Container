<?php
/**
 * ClanCats Container
 *
 * @link      https://github.com/ClanCats/Container/
 * @copyright Copyright (c) 2016-2017 Mario Döring
 * @license   https://github.com/ClanCats/Container/blob/master/LICENSE (MIT License)
 */
namespace ClanCats\Container;

use Composer\Script\Event;
use Composer\Package\CompletePackage;
use Composer\IO\ConsoleIO;

class ComposerContainerFileLoader 
{
    /**
     * This method will generate a mapping file 
     * of container files found in required composer packages.
     * This mapping file can be imported into a container namespace.
     *
     * @param Event             $event
     */
    public static function generateMap(Event $event)
    {
        // prepare the container file mapping array
        $mapping = [];

        // get all available packages
        $packages = $event->getComposer()->getRepositoryManager()->getLocalRepository()->getCanonicalPackages();

        foreach ($packages as $package) 
        {
            if ($package instanceof CompletePackage) 
            {
                $extra = $package->getExtra();

                if (isset($extra['container']) && !empty($extra['container'])) 
                {
                    foreach($extra['container'] as $containerImportName => $containerFileName)
                    {
                        // special case for the main file
                        if ($containerImportName === '@main') {
                            $containerImportName = $package->getName();
                        } else {
                            $containerImportName = $package->getName() . '/' . $containerImportName;
                        }

                        $event->getIO()->write('found <fg=blue>'. $containerImportName .'</>', true, ConsoleIO::VERBOSE);

                        // also prefix the container file with package name
                        $containerFileName = $package->getName() . '/' . $containerFileName;

                        // assign the mapping
                        $mapping[$containerImportName] = '__DIR__' . $containerFileName;
                    }
                }
            }
        }

        $mappingFilePath = $event->getComposer()->getConfig()->get('vendor-dir') . '/container_map.php';

        // define the mapping file
        $mappingFileHeader = "<?php\n\$vendorDir = __DIR__ . '/';\n\nreturn ";
        $mappingFileContent = str_replace('\'__DIR__', '$vendorDir . \'', var_export($mapping, true));

        // store it
        file_put_contents($mappingFilePath, $mappingFileHeader . $mappingFileContent . ';');

        $event->getIO()->write("<fg=green>Generated container map file</>");
    }
}   