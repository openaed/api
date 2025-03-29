![alt](https://openaed.eu/assets/branding/openaed_Woordmerk.svg)

# openaed-api

This is the back-end of OpenAED, the place where all data goes through.
It offers some convenient features, such as;

-   Finding all AEDs in a certain radius from a point
-   Finding all AEDs within an area

## Installation

1. Clone the repository
2. Run `npm install`
3. Run `composer install`
4. Run `php artisan serve` (or use a different hosting solution)

OpenAED is built on Postgres/PostGIS.

## API

The API offers the following endpoints:

-   `GET /api/defibrillators` - Get all AEDs
-   `GET /api/defibrillators/{id}` - Get a specific AED
-   `GET /api/defibrillators/area` - Get all AEDs within an area
    -   Supply at least 3 points in the `points` key:
        ```JSON
        {
          "points": [
            [1, 2],
            [3, 4],
            [1, 2]
          ]
        }
        ```
    -   Make sure the first and last point are the same, to close the area.
-   `GET /api/defibrillators/nearby/{latitude}/{longitude}/{radius}` - Get all AEDs within a certain radius from a point
    -   Radius is in metres
    -   Defibrillator objects will have a key 'distance', also in metres, and will be sorted closest -> furthest

### Authentication

The API requires an access token to be passed in the `Authorization` header.
This token can be generated using the `api:new-access-token` command.

## Commands

A few commands are available to manage the data:

-   `php artisan aed:import {--full}` - Import AEDs from OpenStreetMap
    -   `--full` will request all AEDs, regardless of their last updated date
-   `php artisan api:new-access-token` - Generate a new access token for the API

## Production access

The production instances are:

-   [https://nl.api.openaed.eu](https://nl.api.openaed.eu) - Netherlands

To access the production instance, you need to have an access token.
These can be requested via an email to [api@openaed.eu](mailto:api@openaed.eu). Please include your name, and the instance(s) you want to access.

## License

This project's code is licensed under the [GNU General Public License v3.0](./COPYING)
The data supplied by OpenStreetMap is licensed under the [Open Data Commons Open Database License (ODbL)](https://opendatacommons.org/licenses/odbl/) by the OpenStreetMap Foundation (OSMF).
Any data provided by OpenAED is licensed under the same terms.
