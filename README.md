# CreateEventCalendar #

CreateEventCalendar uses template variables to generate iCalendar files (.ics) specially for events. CreateEventCalendar makes it possible to dynamically create iCalendar files which visitors can download and add to their personal calendar.

**Features**
* Add an URL
* Add an attachment 
* Add a location (and add a map for iOS) with optional automatic geocoding of the address.

### Usage ###
```
Add snippet
[[CreateEventCalendar? 
  &filePath=`/events/`
  &summary=`[[*description]]`
  &startDate=`[[*eventStartDate]]`
  &endDate=`[[*eventEndDate]]`
  &link=`[[*eventLink]]`
  &attachment=`[[*eventAttachment]]`
  &address=`zoom,1,9231DX,Surhuisterveen,Nederland`
  &geocode=`1`
]]

Add placeholder
<a href="[[+calendarLink]]" target="_blank">Add to calendar</a>
```

## Properties ##

| Property    | Description                                                                                  | Example                               |
|-------------|----------------------------------------------------------------------------------------------|---------------------------------------|
| filePath    | Path where the calendar file will be saved.                                                  | /events/                              |
| fileName    | Name of file, defaults to pagetitle. Extensions ".ics" is automatically added.               | myevent                               |
| tpl         | Chunk to use for rendering output.                                                           | eventCalendarItem                     |
| summary     | Add summary to event                                                                         | [[*description]]                      |
| startDate   | Start date of event. Format like 2015-05-14 15:53:00 (default output of date TV)             | 2017-01-17 15:30:00                   |
| endDate     | End date of event. Format like 2015-05-14 15:53:00 (default output of date TV)               | 2017-01-17 18:30:00                   |
| address     | Comma delimited list of the addres as street,housenumber,zipcode,city,country                | georgestreet,1,1234ab,new york,usa    |
| link        | Link to add to the ICS file.                                                                 | http://www.website.nl                 |
| attachment  | Add a file to ICS file.                                                                      | http://www.website.nl/img/myimage.jpg |
| coordinates | Comma delimited coordinates to add location (maps voor IOS). Format like: latitude,longitude | 53.1755331,6.1831149                  |
| geocode     | 0/1. If set to 1, uses address property to geocode and create coordinates automatically.     | 1                                     |