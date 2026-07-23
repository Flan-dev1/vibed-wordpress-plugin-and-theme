# StarFlan Real Estate Data 2.3.0: hierarchical cities

Version 2.3.0 adds city-to-city hierarchy, direct and recursive subcity counts, and inherited property filtering. It is backward-compatible with cities that have no parents: their saved property assignments and filter behavior remain unchanged.

## What changed

### Cities can be subcities

Every `sf_city` post is still a normal city. A city becomes a subcity by saving one or more parent city IDs in the new `_sf_parent_city_ids` post-meta field. Multiple parents are supported, so the hierarchy is a directed acyclic graph rather than a strict tree.

For example:

```text
Metro Manila (#10)
├── Makati (#20)
│   └── Downtown (#40)
└── Taguig (#30)
    └── Downtown (#40)
```

`Downtown` is one city record with two parents. It is not copied or converted to another post type.

Cycles are rejected when a city is saved. A city cannot be its own parent, and a city cannot be assigned below one of its existing descendants. For example, assigning `Downtown (#40)` as a parent of `Metro Manila (#10)` returns:

```text
A city cannot be its own parent or a child of one of its subcities.
```

### Subcity counts are exposed

Two count meanings are available:

- `subcity_count` is the number of immediate children.
- `descendant_count` is the number of unique children at every depth.

In the example above, Metro Manila has a direct count of `2` and a recursive count of `3`. Downtown is counted once even though two hierarchy paths reach it.

### Property membership is inherited upward

Property IDs remain directly assigned to individual cities in `_sf_estatik_property_ids`. Inherited membership is calculated at query time; no duplicate property assignment is written to parent cities.

If the assignments are:

```text
Metro Manila (#10): [100]
Makati (#20):       [200]
Taguig (#30):       [300]
Downtown (#40):     [400, 200]
```

then the effective results are:

```text
Downtown:     [400, 200]
Makati:       [200, 400]
Taguig:       [300, 400, 200]
Metro Manila: [100, 200, 300, 400]
```

IDs are normalized and deduplicated. Selecting a subcity filters to that subcity and its own descendants; it does not include sibling or parent-only properties.

## Managing the hierarchy

### WordPress admin

The City add/edit form now contains a **Parent Cities** multi-select. Use Ctrl on Windows/Linux or Command on macOS to select multiple parents. Leaving the field empty creates a top-level city.

The existing **Estatik Properties** picker still controls direct assignments. Assign a property to the most specific applicable city; parent-city results inherit it automatically.

### CSV imports

City CSV files now have this header:

```csv
name,image,parent_cities,properties
```

`parent_cities` accepts WordPress city post IDs separated by commas, semicolons, or spaces. Because commas delimit CSV columns, quote comma-separated lists.

Example input:

```csv
name,image,parent_cities,properties
Metro Manila,,,
Makati,,10,"200,201"
Downtown,,"20,30",400
```

Example result, assuming the referenced city IDs already exist:

```text
Import complete: 3 created, 0 skipped.
```

Parent cities must exist before their children are imported. An unknown parent ID causes that row to be skipped. The bundled `samples/cities.csv` includes the new column.

## PHP API

The global helpers are intended for themes and other plugins.

### Read subcities and counts

```php
$direct_ids       = starflan_get_subcity_ids( 10 );
$descendant_ids   = starflan_get_subcity_ids( 10, true );
$direct_count     = starflan_get_subcity_count( 10 );
$descendant_count = starflan_get_subcity_count( 10, true );

var_export( compact( 'direct_ids', 'descendant_ids', 'direct_count', 'descendant_count' ) );
```

Example output:

```php
array (
  'direct_ids' => array ( 0 => 20, 1 => 30 ),
  'descendant_ids' => array ( 0 => 20, 1 => 30, 2 => 40 ),
  'direct_count' => 2,
  'descendant_count' => 3,
)
```

The class API also exposes parents and ancestors:

```php
use StarFlan\RealEstate\CityHierarchy;

CityHierarchy::parent_ids( 40 );   // [20, 30]
CityHierarchy::ancestor_ids( 40 ); // [20, 30, 10]
```

All traversal methods return unique integer IDs. Invalid/non-city IDs return an empty array or a count of zero.

### Read effective property IDs

```php
$with_children = starflan_get_city_property_ids( 10 );
$direct_only   = starflan_get_city_property_ids( 10, false );

print_r( $with_children ); // [100, 200, 300, 400]
print_r( $direct_only );   // [100]
```

### Filter a custom property query

```php
$args = array(
    'post_type'      => 'properties',
    'post_status'    => 'publish',
    'posts_per_page' => 12,
);

$args  = starflan_filter_properties_by_city( $args, 10 );
$query = new WP_Query( $args );
```

Example filtered arguments:

```php
array (
  'post_type' => 'properties',
  'post_status' => 'publish',
  'posts_per_page' => 12,
  'post__in' => array ( 100, 200, 300, 400 ),
)
```

If the input arguments already contain `post__in`, the helper intersects that restriction with the city's effective IDs. If the city has no properties, it sets `post__in` to `[0]`; this is important because WordPress treats an empty `post__in` as no restriction.

## Shortcode filtering

The existing `[properties]` shortcode accepts a city through the `city-id` query parameter. It now includes descendant assignments automatically.

```text
/properties-list/?city-id=10&status=for-sale&sort=lowest_price
```

With the sample hierarchy, this returns for-sale properties assigned directly to Metro Manila, Makati, Taguig, or Downtown. Pagination now uses the filtered query's page count and retains the city, status, and sort parameters. The accidental `true` text previously emitted when a city filter was active has also been removed.

An invalid `city-id` is ignored, preserving the shortcode's prior behavior.

## REST API

City responses from `/wp-json/wp/v2/sf_city/{id}` now include computed read-only fields. For city `10`, a response contains:

```json
{
  "id": 10,
  "parent_city_ids": [],
  "direct_subcity_ids": [20, 30],
  "descendant_city_ids": [20, 30, 40],
  "subcity_count": 2,
  "descendant_count": 3,
  "effective_property_ids": [100, 200, 300, 400],
  "meta": {
    "_sf_parent_city_ids": [],
    "_sf_estatik_property_ids": [100]
  }
}
```

The existing REST permissions for the non-public `sf_city` post type still apply. `_sf_parent_city_ids` is registered as an array of integers and can be written in edit context by users who can edit posts. Computed fields cannot be written directly.

Example update request body:

```json
{
  "meta": {
    "_sf_parent_city_ids": [20, 30]
  }
}
```

Admin, CSV, and REST saves validate that every parent is a city and reject self-references and hierarchy cycles. WordPress REST meta sanitization also normalizes the value to a unique array of positive integers.

## Extension hooks

Version 2.3.0 adds these filters:

```php
// Filter immediate children of a city.
add_filter( 'starflan_city_direct_subcity_ids', function ( $ids, $city_id ) {
    return $ids;
}, 10, 2 );

// Filter all unique descendants after traversal.
add_filter( 'starflan_city_descendant_ids', function ( $ids, $city_id ) {
    return $ids;
}, 10, 2 );

// Filter all ancestors after traversal.
add_filter( 'starflan_city_ancestor_ids', function ( $ids, $city_id ) {
    return $ids;
}, 10, 2 );

// Filter effective IDs. $member_city_ids includes the selected city.
add_filter( 'starflan_city_property_ids', function ( $property_ids, $city_id, $include_descendants, $member_city_ids ) {
    return $property_ids;
}, 10, 4 );

// Modify final WP_Query arguments used for a city restriction.
add_filter( 'starflan_city_property_query_args', function ( $args, $city_id, $property_ids ) {
    return $args;
}, 10, 3 );
```

Callbacks that change ID arrays should return positive WordPress post IDs. The plugin normalizes hierarchy and property filter output before use where documented.

## Storage and compatibility notes

- Plugin version and asset version are now `2.3.0`.
- New metadata: `_sf_parent_city_ids`, stored as one array of integer city IDs per `sf_city` post.
- Existing `_sf_estatik_property_ids` data is unchanged.
- Existing cities require no migration and behave as top-level cities until parents are assigned.
- Deleting or deactivating the plugin continues to retain data.
- Traversals protect themselves with a visited set, so legacy/corrupt cyclic data cannot cause infinite recursion; normal plugin saves prevent new cycles.
- Descendant and inherited-property order is breadth-first and deterministic by city ID before extension filters alter it. Consumers should treat the result as an ID set rather than depend on display order.

## Verification

Run the lightweight hierarchy regression test from the plugin directory:

```bash
php tests/city-hierarchy.php
```

Expected output:

```text
City hierarchy tests passed.
```

Lint all PHP files before deployment:

```bash
find . -name '*.php' -print0 | xargs -0 -n1 php -l
```
