Exchanges sind globale Namensräume zum Transport von Nachrichten über Queues und/oder RoutingKeys an Consumer

1. Konfiguration Exchanges
---------------------------------------------------

Um Kollisionen bei der Arbeit mit einem RabbitMQ Server zu vermeiden müssen die Exchanges eindeutig benannt werden !

1.1 Global festgelete Namenskonventionen für laufende Prozesse zwischen verschiedenen Themengebieten

_[ExchangeName] 
z.B.
_SMSFlow 

1.2 Für spezielle geschlossene fachliche Projekte
---------------------------------------------------

_[Projektname]_[ExchangeName] 
z.B.
_TI_DataFlow

1.3 Für Mitarbeiterprojekte / Tests etc.
---------------------------------------------------

[Mitarbeitername]_[ExchangeName]
z.B.
Krueger_TestSMSFlows

2. Exchange Typen
- Normal - Nach Neustart des Servers bleiben Messages nicht erhalten
- Dauerhaft/Persistent - Nach Neustart RabbitMQ bleiben Messages in Exchanges erhalten

Vordefinierte Typen
* "Default Exchange" - Wenn Queues ohne Bindung an einen Exchhange benutzt werden, sind diese an diesen "Default Exchange" gebunden
* "Direct Exchange"  - Nachrichten werden explizit nur nach exakten Match an diesen Exchange gesendet
* "Topic Exchange"   - Bindung läuft über Routing Key ( Matching auf Routing Key Namen )
* "Fanout Exchange"  - Nachricht wird "fächerartig" an alle an diesen Exchange gebundenen Queues repliziert
* "Dead Letter Exchange" - Alle Nachrichten welche nicht geroutet werden können bzw. nicht auf Queues gematcht werden können, landen in dieser Queue. Damit diese Nachrichten nicht sofort gelöscht wersden, muß dieser Exchange explizit angegeben werden ! 

3. Exchange/Queues
An einen Exchanges können mehr mehrere Queues gebunden werden
