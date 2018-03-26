# CalDav Calendar-XH
The CalDac Calendar is a plugin for CMSimple-XH to integrate calendar data from a caldav server. It is tested with Google calendars as well as the OwnCloud calendar server. From one server different calendars can be used. The plugin still is under developement and has only restricted functionality. Different formats and a filter function are planned.

## Backend
The backend of CMSimple-XH offers an easy way to change the settings of the plugin. In the configuration the url, user and password for the CalDav server access are set, as well as a list of fields, that are shown. The max days paramters let you restrict the number of displayed events in the list view. Formatting is done by css, which also can be altered in the backend.

## Usage
The integration is done with a simple plugin call on a CMSimple-XH page by adding {{{caldav-calendar('calendar name');}}}. The calender name parameter determins, which calender is used from the server.

## Display
In the current state of developement only events in the future are displayed. 