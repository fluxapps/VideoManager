VideoManager
============
### Documentation: 
[Documentation.pdf](/doc/Documentation.pdf?raw=true)

### Installation
#### Install MediaConverter
This plugin requires MediaConverter.
In order to install the MediaConverter plugin go into ILIAS root folder and use:

```bash
mkdir -p Customizing/global/plugins/Services/Cron/CronHook
cd Customizing/global/plugins/Services/Cron/CronHook
git clone https://github.com/studer-raimann/MediaConverter.git
```

#### Install CtrlMainMenu
For the VideoManager to work properly, you also need to install the CtrlMainMenu­Plugin. Follow these
commands:

```bash
mkdir -p Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/
cd Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/
git clone https://github.com/studer-raimann/CtrlMainMenu.git
```

#### Install ffmpeg
This plugin requires ffmpeg. If not yet installed (you can test it by typing 'ffmpeg' in a console), download it from: https://www.ffmpeg.org/download.html
Or, if you're using Ubuntu, you can install ffmpeg by typing the following commands in your terminal:
```bash
sudo add-apt-repository ppa:mc3man/trusty-media && sudo apt-get update
sudo apt-get install ffmpeg
```
After installing, add the path to your installation:
Either in the ilias setup under Basic Settings -> Optional Third-Party Tools -> Path to ffmpeg, write '/usr/bin/ffmpeg'
or directly into the file ilias.ini.php -> [tools] -> ffmpeg = "/usr/bin/ffmpeg"

#### Install the plugin
Start at your ILIAS root directory
```bash
mkdir -p Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/
cd Customizing/global/plugins/Services/UIComponent/UserInterfaceHook
git clone https://github.com/studer-raimann/VideoManager.git
```
As ILIAS administrator go to "Administration->Plugins" and install/activate the plugin.

#### Additional Plugins
[VideoManagerTME](https://github.com/studer-raimann/VideoManagerTME)

### ILIAS Plugin SLA

Wir lieben und leben die Philosophie von Open Source Software! Die meisten unserer Entwicklungen, welche wir im Kundenauftrag oder in Eigenleistung entwickeln, stellen wir öffentlich allen Interessierten kostenlos unter https://github.com/studer-raimann zur Verfügung.

Setzen Sie eines unserer Plugins professionell ein? Sichern Sie sich mittels SLA die termingerechte Verfügbarkeit dieses Plugins auch für die kommenden ILIAS Versionen. Informieren Sie sich hierzu unter https://studer-raimann.ch/produkte/ilias-plugins/plugin-sla.

Bitte beachten Sie, dass wir nur Institutionen, welche ein SLA abschliessen Unterstützung und Release-Pflege garantieren.

### Contact
info@studer-raimann.ch  
https://studer-raimann.ch  

