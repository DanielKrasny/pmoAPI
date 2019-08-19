# pmoAPI
>This repository is no longer supported. Use [PovodiAPI](https://github.com/DanielKrasny/PovodiAPI)

![pmoAPI](https://github.com/DanielKrasny/pmoAPI/raw/master/headerimage.jpeg)
Get water info in a simple API. This PHP script supports [Povodí Moravy](http://www.pmo.cz).
## Usage 
### Nádrže
```
./pmoapi.php?channel=nadrze&station=1&response=json&values=all
```
Required to enter:

- *channel*: **nadrze** (used for this example), **sap**, **srazky**
- *station*: ID of station, available in [/stations](https://github.com/DanielKrasny/pmoAPI/tree/master/stations)
- *response*: **json**, **rss**
- *values*: **all**, **latest**¨

### SaP (Stavy a průtoky)
```
./pmoapi.php?channel=sap&station=045&response=json&values=all
```
Required to enter:
- *channel*: **nadrze**, **sap** (used for this example), **srazky**
- *station*: ID of station, available in [/stations](https://github.com/DanielKrasny/pmoAPI/tree/master/stations)
- *response*: **json**, **rss**
- *values*: **all**, **latest**

### Srážky
```
./pmoapi.php?channel=srazky&station=76&response=json&values=latest
```
Required to enter:
- *channel*: **nadrze**, **sap**, **srazky** (used for this example)
- *station*: ID of station, available in [/stations](https://github.com/DanielKrasny/pmoAPI/tree/master/stations)
- *response*: **json**, **rss**
Optional:
- *values*: **all**, **latest** (including total value), **total**
- *temp*: Only for `rss` response, channel `srazky` and set values to `all`. Allow showing temperature in title. Options: **yes**, **no**

##### License
The script is available under the [MIT license](https://github.com/DanielKrasny/pmoAPI/master/LICENSE).
