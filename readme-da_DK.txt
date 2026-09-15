=== Plugin-tilstandskontrol ===
Contributors: michelechesi
Tags: plugins, vedligeholdelse, sikkerhed, opdateringer
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.1.14
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Kontrollerer alle installerede plugins mod WordPress.org-kataloget og markerer lukkede, forældreløse eller længe uvedligeholdte plugins.

== Description ==

Ved hver kontrol gennemsøger Plugin-tilstandskontrol alle installerede plugins og sammenligner dem med WordPress.org-kataloget. Den registrerer:

* Plugins, der er blevet fjernet fra kataloget (f.eks. på grund af sikkerhedshuller eller overtrædelse af retningslinjer)
* Plugins, der ikke har fået en opdatering i mange måneder
* Plugins, der kun er testet mod en markant ældre WordPress-version

Resultaterne vises direkte som en ekstra kolonne i den almindelige plugin-oversigt samt på en separat rapportside under **Værktøjer → Plugin-tilstand**. Grænseværdierne for "Kontrollér" og "Handling nødvendig" kan indstilles frit. E-mailnotifikation er som standard aktiveret (månedligt) og kan tilpasses eller deaktiveres i indstillingerne.

**Bemærkning om værdien „Testet op til":** Denne værdi vedligeholdes af den enkelte plugin-forfatter selv og kontrolleres ikke af nogen. Et fund herom er en indikation, ikke et bevis — datoen for sidste opdatering siger som regel mere om den faktiske vedligeholdelsestilstand.

== Installation ==

1. Download den aktuelle ZIP-fil fra [GitHub-repositoriet](https://github.com/Michele64/wp-plugin-health-check/releases)
2. Upload den under **Plugins → Tilføj nyt → Upload plugin**, eller udpak den i `wp-content/plugins/`
3. Aktivér pluginet

Opdateringer hentes automatisk via GitHub-repositoriet — ingen separat opdateringsserver nødvendig.

== Changelog ==

= 1.1.14 =
* "Vis detaljer"-popuppen (beskrivelse, installation, changelog, opgraderingsbeskeder) lokaliseres nu også, i stedet for altid at vise den tyske readme.txt. Der medfølger en readme-{locale}.txt i samme format for hvert understøttet sprog, som automatisk hentes ud fra sidens sprog. Den korte beskrivelse under plugin-navnet i plugin-listen oversættes nu også.
* Tilføjet en "Upgrade Notice"-sektion til readme'en (fandtes ikke tidligere)

= 1.1.13 =
* Tilføjet oversættelser til dansk, italiensk og fransk

= 1.1.12 =
* Tilføjet engelsk oversættelse (en_US og en_GB). Tidligere indeholdt /languages-mappen kun .pot-skabelonen, ingen kompileret .mo-fil — da kildeteksterne selv er på tysk, viste enhver ikke-tysk side de tyske originaltekster i mangel af en oversættelse.

= 1.1.11 =
* Opfølgning på 1.1.10: Scroll-til-top efter "Kontrollér nu" virkede ikke pålideligt, fordi det blev udført for tidligt (før browserens egen senere gendannelse af rulleposition). Det tvinges nu også igennem ved load-event og med en kort forsinkelse; redirect-URL'en er desuden unik for hver kontrol (tidsstempel i stedet for en fast værdi).

= 1.1.10 =
* Rettet: Efter "Kontrollér nu" endte siden ved den sidst besøgte rulleposition i stedet for ved den friske rapport, fordi redirect-URL'en er identisk ved hver kontrol, og browseren derfor gendanner sin huskede rulleposition for den. Rullepositionen sættes nu eksplicit til toppen af siden.

= 1.1.9 =
* Rettet: Efter lagring af indstillinger sprang siden til toppen i stedet for at vende tilbage til indstillingssektionen (redirect manglede anker)

= 1.1.8 =
* E-mailnotifikation understøtter nu flere, kommaseparerede modtageradresser. Hver adresse valideres individuelt; ugyldige adresser forkastes ved lagring og markeres med en besked.

= 1.1.7 =
* Rettet en fejlagtig "Handling nødvendig"-status for plugins med egen opdateringskontrol (inklusive dette plugin selv, via GitHub): Tjekket "kendt i WordPress.org-kataloget" kiggede tidligere kun efter, om der overhovedet fandtes en post i update_plugins-transienten — men den udfyldes også af tredjeparts-opdateringsværktøjer, ikke kun af WordPress.org. Nu tjekkes desuden id-formatet ("w.org/plugins/…"), som kun rigtige wordpress.org-poster har.

= 1.1.6 =
* Tilføjet valgfri GitHub-godkendelse til opdateringskontrollen (konstanten WPHC_GITHUB_TOKEN i wp-config.php) for at undgå 403-fejl fra GitHub-rategrænser eller IP-baseret misbrugsdetektion ved shared hosting

= 1.1.5 =
* E-mailnotifikation er nu aktiveret som standard (tidligere: deaktiveret)
* Tilføjet en aktiveringshook, så cron rent faktisk sættes op til at matche standarden ved en frisk installation

= 1.1.4 =
* Ændret standardfrekvensen for e-mailnotifikation til "månedligt" (tidligere: "ugentligt")

= 1.1.3 =
* Versionsnummeret læses nu dynamisk fra plugin-headeren i stedet for også at stå fast i koden
* Opdateret "Testet op til" til 7.1

= 1.1.2 =
* Første udgivelse via det offentlige GitHub-repo med automatiske opdateringer (Plugin Update Checker)
* Forberedt til flere sprog: alle synlige tekster kan oversættes via tekstdomænet "wp-plugin-health-check"
* Tilføjet README.md og readme.txt

Tidligere versionsnoter (før flytningen til GitHub) er ikke dokumenteret i detaljer. Ældre commits kan findes i [GitHub-commit-historikken](https://github.com/Michele64/wp-plugin-health-check/commits/main).

== Upgrade Notice ==

= 1.1.13 =
Tilføjer dansk, italiensk og fransk oversættelse. Ingen handling nødvendig.

= 1.1.8 =
E-mailnotifikation accepterer nu en kommasepareret liste af adresser. Har du kun brugt én adresse, ændres der ikke noget for dig.

= 1.1.6 =
Viser opdateringskontrollen en GitHub 403-fejl, kan du nu sætte WPHC_GITHUB_TOKEN i wp-config.php for at løse det — se pluginets README for detaljer.
