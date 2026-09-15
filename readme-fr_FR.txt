=== Vérification de l'état des plugins ===
Contributors: michelechesi
Tags: extensions, maintenance, sécurité, mises à jour
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.1.14
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Vérifie toutes les extensions installées par rapport à l'annuaire WordPress.org et signale celles qui sont fermées, orphelines ou non maintenues depuis longtemps.

== Description ==

À chaque vérification, Vérification de l'état des plugins analyse toutes les extensions installées et les compare à l'annuaire WordPress.org. Elle détecte :

* Les extensions retirées de l'annuaire (par exemple en raison de failles de sécurité ou de violations des règles)
* Les extensions n'ayant reçu aucune mise à jour depuis de nombreux mois
* Les extensions testées uniquement avec une version de WordPress nettement plus ancienne

Les résultats apparaissent directement dans une colonne supplémentaire de la liste standard des extensions, ainsi que sur une page de rapport dédiée sous **Outils → État des extensions**. Les seuils pour « À vérifier » et « Action requise » sont librement configurables. La notification par e-mail est activée par défaut (mensuelle) et peut être ajustée ou désactivée dans les réglages.

**Remarque sur la valeur « Testé jusqu'à » :** cette valeur est renseignée par l'auteur de l'extension lui-même et n'est vérifiée par personne d'autre. Un constat basé sur cette valeur est donc un indice, pas une preuve — la date de la dernière mise à jour en dit généralement plus long sur l'état réel de maintenance.

== Installation ==

1. Télécharger le fichier ZIP actuel depuis le [dépôt GitHub](https://github.com/Michele64/wp-plugin-health-check/releases)
2. Le charger via **Extensions → Ajouter → Téléverser une extension**, ou le décompresser dans `wp-content/plugins/`
3. Activer l'extension

Les mises à jour sont récupérées automatiquement depuis le dépôt GitHub — aucun serveur de mise à jour séparé n'est nécessaire.

== Changelog ==

= 1.1.14 =
* La fenêtre « Afficher les détails » (Description, Installation, Changelog, Avis de mise à jour) est désormais aussi localisée, au lieu d'afficher systématiquement le readme.txt allemand. Un fichier readme-{locale}.txt au même format est fourni pour chaque langue prise en charge et est utilisé automatiquement selon la langue du site. La courte description sous le nom de l'extension dans la liste des extensions est également traduite désormais.
* Ajout d'une section « Upgrade Notice » au readme (elle n'existait pas auparavant)

= 1.1.13 =
* Ajout des traductions danoise, italienne et française

= 1.1.12 =
* Ajout d'une traduction anglaise (en_US et en_GB). Auparavant, le dossier /languages ne contenait que le modèle .pot, aucun fichier .mo compilé — les textes source étant eux-mêmes en allemand, tout site non germanophone affichait le texte original allemand faute de traduction.

= 1.1.11 =
* Suite de la 1.1.10 : le retour en haut de page après « Vérifier maintenant » ne fonctionnait pas de façon fiable car il s'exécutait trop tôt, avant que le navigateur ne restaure plus tard sa propre position de défilement. Il est désormais également forcé lors de l'événement load et après un court délai ; l'URL de redirection est en outre unique à chaque vérification (horodatage au lieu d'une valeur fixe).

= 1.1.10 =
* Corrigé : après « Vérifier maintenant », la page se retrouvait à la dernière position de défilement visitée au lieu du rapport tout juste généré, car l'URL de redirection est identique à chaque vérification et le navigateur restaure alors sa position de défilement mémorisée pour celle-ci. Le défilement est désormais explicitement ramené en haut de la page.

= 1.1.9 =
* Corrigé : après l'enregistrement des réglages, la page sautait en haut au lieu de revenir à la section des réglages (la redirection n'avait pas d'ancre)

= 1.1.8 =
* La notification par e-mail prend désormais en charge plusieurs adresses destinataires séparées par des virgules. Chaque adresse est validée individuellement ; les adresses invalides sont rejetées à l'enregistrement et signalées par un avis.

= 1.1.7 =
* Correction d'un résultat erroné « Action requise » pour les extensions disposant de leur propre vérificateur de mises à jour (y compris cette extension elle-même, via GitHub) : la vérification « connue dans l'annuaire WordPress.org » ne cherchait auparavant qu'une entrée dans le transient update_plugins — or celui-ci est aussi alimenté par des vérificateurs tiers, pas seulement par WordPress.org. Le format d'id (« w.org/plugins/… »), propre aux seules entrées provenant réellement de wordpress.org, est désormais également vérifié.

= 1.1.6 =
* Ajout d'une authentification GitHub optionnelle pour le vérificateur de mises à jour (constante WPHC_GITHUB_TOKEN dans wp-config.php) afin d'éviter les erreurs 403 dues aux limites de fréquence de GitHub ou à la détection d'abus basée sur l'IP en hébergement mutualisé

= 1.1.5 =
* La notification par e-mail est désormais activée par défaut (auparavant : désactivée)
* Ajout d'un hook d'activation afin que le cron soit effectivement configuré selon la valeur par défaut lors d'une nouvelle installation

= 1.1.4 =
* Fréquence par défaut de la notification par e-mail modifiée en « mensuelle » (auparavant : « hebdomadaire »)

= 1.1.3 =
* Le numéro de version est désormais lu dynamiquement depuis l'en-tête de l'extension, au lieu d'être aussi codé en dur
* Mise à jour de « Testé jusqu'à » vers la version 7.1

= 1.1.2 =
* Première publication via le dépôt GitHub public avec mises à jour automatiques (Plugin Update Checker)
* Préparation au multilinguisme : tous les textes visibles sont traduisibles via le domaine de texte « wp-plugin-health-check »
* Ajout de README.md et readme.txt

Les notes de version antérieures (avant le passage à GitHub) ne sont plus documentées en détail. Les anciens commits sont consultables dans l'[historique des commits GitHub](https://github.com/Michele64/wp-plugin-health-check/commits/main).

== Upgrade Notice ==

= 1.1.13 =
Ajoute les traductions danoise, italienne et française. Aucune action requise.

= 1.1.8 =
La notification par e-mail accepte désormais une liste d'adresses séparées par des virgules. Si vous n'utilisiez qu'une seule adresse, rien ne change pour vous.

= 1.1.6 =
Si la vérification des mises à jour affiche une erreur GitHub 403, vous pouvez désormais définir WPHC_GITHUB_TOKEN dans wp-config.php pour la résoudre — voir le README de l'extension pour plus de détails.
