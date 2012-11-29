About
===================


A PHP abstraction library for JavaScript charting built as a Symfony 2 service bundle.

The following libraries are linked to this bundle via git submodules:

* Jquery
* Flot
* jqPlot

You can jump to any version by checking out the version you want to use via the git submodule.


Getting Started
===================

* Prerequisites: You need a working Symfony2 framework installed and setup. 

**IMPORTANT - Please follow the directions in order or the JS library dependencies will not be properly built**

1. Add the AltamiraBundle ScriptHandler to the ```post-install-cmd``` and ```post-update-cmd``` section in scripts in your composer.json file.
    
    ```yaml
        "scripts": {
            "post-install-cmd": [
                "Malwarebytes\\AltamiraBundle\\ScriptHandler::installJSDependencies",
                "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::buildBootstrap",
                "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::clearCache",
                "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::installAssets",
                "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::installRequirementsFile"
    
            ],
            "post-update-cmd": [
                "Malwarebytes\\AltamiraBundle\\ScriptHandler::installJSDependencies",
                "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::buildBootstrap",
                "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::clearCache",
                "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::installAssets",
                "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::installRequirementsFile"
            ]
        }
    ```
    
    **NOTE** You must add our bundle first before installAssets or you will have to re-run install assets manually.
    
    
2. Now install the altamira-bundle via composer.
    
    
    From your main symfony2 directory, run:
    
    ``` bash
    $ composer require malwarebytes/altamirabundle:dev-master
    ```
    
    
3. Enable the bundle within symfony:
    
    ``` php
    <?php
    // app/AppKernel.php
    
    public function registerBundles()
    {
         $bundlles = array (
             // ...
             new Malwarebytes\AltamiraBundle\MalwarebytesAltamiraBundle(),
         );
    }
    ```
    
    
    
4. If you would like to see example code, enable the example controller:
    
    ``` yml
    # app/config/routing.yml
    
    altamira_example:
        resource: "@MalwarebytesAltamiraBundle/Resources/config/routing.yml"
        prefix:   /chart_demo
    ```

Troubleshooting
==================

If there are javascript files missing when you view the example, the JS dependencies may not have been fetched properly. Check that the folders in ```Resources/public/js/``` are not empty.

If code exists in the libs folder, the assets may have failed to be installed into the web folder. Run the following to regenerate the assets:


```bash
$ app/console assets:install
```


Developing
===================

Refer to the sample controller on examples on how to use the code.
