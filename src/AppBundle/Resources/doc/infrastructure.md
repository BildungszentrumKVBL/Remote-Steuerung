Create your infrastructure
==========================

You can use either YAML, CSV or XML to model your infrastructure.
YAML is recommended.


# YAML

YAML is a tree-like structure, but very picky about spacing. So please keep them consistent.

```yaml
'Main':
    'Room1':
        'Zulu': 'IP.Of.The.MicroController'
        'PC':
            'name': 'Reachable.Hostname.Or.IP'
            
    'AnotherRoom':
        'Zulu': 'IP.Of.This.MicroController'
        'PC':
            'name': 'Reachable.Hostname.Or.Ip'
            
'Another':
...
```


# CSV

Please make sure to label the parts in the CSV. And also keep the right order!
The CSV has to be separated with semicolons (;).

```csv
{{Building}}
Main
Another

{{Rooms}}
Main;Room1
Main;AnotherRoom
Another;...

{{PC}}
Room1;Reachable.Hostname.Or.IP
AnotherRoom;Reachable.Hostname.Or.Ip

{{Zulu}}
Room1;IP.Of.This.MicroController
AnotherARoom;IP.Of.This.MicroController
```


# XML

Keep in mind that a header is required for the XML data. Also, there is a limitation that does not allow rooms and buildings to contain spaces.

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<root>
    <Main>
        <Room1>
            <Zulu>IP.Of.This.MicroController</Zulu>
            <PC>
                <name>Reachable.Hostname.Or.Ip</name>
            </PC>
        </Room1>
        <AnotherRoom>
            <Zulu>IP.Of.This.MicroController</Zulu>
            <PC>
                <name>Reachable.Hostname.Or.Ip</name>
            </PC>
        </AnotherRoom>
    </Main>
    <Another>
        ...
    </Another>
</root>
```
