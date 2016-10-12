VideoManager
============
###Documentation: 
[Documentation.pdf](/doc/Documentation.pdf?raw=true)

###Installation
####Install MediaConverter
This plugin requires MediaConverter.
In order to install the MediaConverter plugin go into ILIAS root folder and use:

```bash
mkdir -p Customizing/global/plugins/Services/Cron/CronHook
cd Customizing/global/plugins/Services/Cron/CronHook
git clone https://github.com/studer-raimann/MediaConverter.git
```

####Install CtrlMainMenu
For the VideoManager to work properly, you also need to install the CtrlMainMenu­Plugin. Follow these
commands:

```bash
mkdir -p Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/
cd Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/
git clone https://github.com/studer-raimann/CtrlMainMenu.git
```

####Install ffmpeg
This plugin requires ffmpeg. If not yet installed (you can test it by typing 'ffmpeg' in a console), download it from: https://www.ffmpeg.org/download.html
Or, if you're using Ubuntu, you can install ffmpeg by typing the following commands in your terminal:
```bash
sudo add-apt-repository 'deb http://ppa.launchpad.net/jon-severinsson/ffmpeg/ubuntu '"$(cat /etc/*-release | grep "DISTRIB_CODENAME=" | cut -d "=" -f2)"' main' && sudo apt-get update
sudo apt-get install ffmpeg
```
After installing, add the path to your installation:
Either in the ilias setup under Basic Settings -> Optional Third-Party Tools -> Path to ffmpeg, write '/usr/bin/ffmpeg'
or directly into the file ilias.ini.php -> [tools] -> ffmpeg = "/usr/bin/ffmpeg"

####Install the plugin
Start at your ILIAS root directory
```bash
mkdir -p Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/
cd Customizing/global/plugins/Services/UIComponent/UserInterfaceHook
git clone https://github.com/studer-raimann/VideoManager.git
```
As ILIAS administrator go to "Administration->Plugins" and install/activate the plugin.

####Additional Plugins
[VideoManagerTME](https://github.com/studer-raimann/VideoManagerTME)

###Contact
studer + raimann ag
Waldeggstrasse 72
3097 Liebefeld
Switzerland

info@studer-raimann.ch
www.studer-raimann.ch
