# SUAP CWOL

## Descrizione

SUAP CWOL è un portale di presentazione istanze online per le attività produttive.

## Per iniziare

### Dipendenze

* PHP >= 5.6.0 (BO e FO)
* MySQL (BO e FO)
* Wordpress 5.0 o successivo (FO)
* Apache Ant (FO)

### BO

#### Installazione

* Creare il file Config.inc.php (vedere *Config.inc.sample.php*)
* Creare la cartella config (vedere la cartella *config.sample*) e configurare i file all'interno
* Lanciare il composer
```
composer install
```
* Creare i database partendo dagli schema presenti nella cartella *dist-utils/schema_InnoDB*

#### Lancio

Accedere all'applicativo richiamando il file Start.php dal web server.

### FO

#### Installazione

* Installare WordPress
* Configurare l'installazione come network ([wordpress.org/documentation/article/create-a-network](https://wordpress.org/documentation/article/create-a-network/))
* Creare il file build.properties (vedere *build.sample.properties*)
* Lanciare la build tramite Ant
```
ant "2. Wordpress build (files)"
```
* Copiare il risultato della build nell'installazione WordPress
* Creare la configurazione per i singoli plugin (vedere relativi file *.sample*)
* Attivare i plugin
