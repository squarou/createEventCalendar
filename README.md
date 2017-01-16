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