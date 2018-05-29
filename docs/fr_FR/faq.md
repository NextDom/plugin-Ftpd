# FAQ

### J'essaie de me connecter au daemon et je ne vois rien

Le daemon est n'autorise pas toutes les commandes FTP. Juste le dépot de fichier pour éviter une faille de sécurité.
Si vous activez le debug vous devriez avoir le message suivant qui indique que la connexion fonctionne : "[DEBUG] : connect: XXXX".


### J'ai des erreurs "unable to solve:"

Votre configuration DNS ne permet pas à Jeedom de retrouver un nom dns de votre équipement. Ce n'est pas grave.


### Je n'arrive pas à créer mes équipements

Les équipements sont créés automatique à la reception de la première image.


### Aucune image n'est sauvegardée

Il faut aller dans la log ftpd_daemon et regarder si des erreurs sont présentes.
Si des erreurs sont présente, les remonter au développeur link:https://www.jeedom.com/forum/viewtopic.php?f=28&t=24684&start=500[via le Forum sur le lien suivant]


### Je souhaite mettre 21 pour le port

Le daemon ne peut écouter sur le port standard ftp (21) car il n'est pas lancé en tant que root.


### J'ai le message de debug : unable to solve: 192.168.1.26 [Errno 1] Unknown host

En fait si la camera a un nom DNS, je la nomme avec son nom DNS sinon elle a pour nom  Addr_<IP>. Ce n'est pas grave, ça veut juste dire que tu n'as pas configuré de résolution DNS inverse pour l'IP en question.


### Je ne vois plus les images arriver après une mise à jour en version 9 de debian ?

Il est nécessaire d'aller réinitialiser le répertoire de stockage dans le menu de configuration du plugin "Reinitialisation du répertoire de stockage des captures".


### Pourquoi le plugin est gratuit ?

Ce plugin est gratuit pour que chacun puisse en profiter simplement. Si vous souhaitez tout de même faire un don au développeur du plugin, merci de me contacter par MP sur le forum.


### J'aimerai remonter des erreurs/modifications directement dans le code ?

C'est tout à fait possible via https://github.com/Jeedom-Plugins-Extra/plugin-ftpd[github]

### Aucune image n'est conservé alors que dans la log en mode debug, il y a plein de chose d'écrit ?

Avez-vous activer l'enregistrement des images sur chaque équipement type camera.
