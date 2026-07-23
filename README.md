# StarFlan Real Estate

StarFlan Real Estate is a WordPress project containing a custom real-estate data plugin and a responsive child theme for Hello Elementor. It connects city and testimonial content managed in WordPress with Estatik property listings, custom shortcodes, sliders, filtered listing pages, and dynamic navigation.

The `main` branch currently contains version `2.2.1` of the StarFlan Real Estate Data plugin.

## Features

- Schema-driven City and Testimonial custom post types
- WordPress admin forms and CSV imports
- Estatik property search and city-to-property assignment
- Property listing filters for status, city, and price/date sorting
- Featured-property and city sliders powered by Swiper
- Paginated city and testimonial displays
- Dynamic header and footer city links
- Responsive property cards, galleries, navigation, and layouts
- REST API exposure for Estatik property prices

## Project structure

```text
starflan-real-estate/
├── plugins/
│   └── starflan-real-estate/
│       ├── assets/                 Plugin CSS, JavaScript, and images
│       ├── includes/shortcodes/    Property-facing shortcodes
│       ├── samples/                Example CSV imports
│       ├── src/                    Data schema, admin, and service classes
│       ├── starflan-real-estate.php
│       └── uninstall.php
└── themes/
    └── hello-elementor-child/
        ├── js/                     Slider and site interactions
        ├── template-parts/         Dynamic header
        ├── functions.php           Theme features and shortcodes
        ├── header.php
        ├── footer.php
        └── style.css
```

## Requirements

- WordPress 6.4 or newer
- PHP 7.4 or newer
- [Hello Elementor](https://wordpress.org/themes/hello-elementor/) parent theme
- [Estatik](https://wordpress.org/plugins/estatik/) with its `properties` post type and taxonomies available
- Elementor for building pages that consume the project shortcodes
- Internet access on the public site for Swiper 12 assets loaded from jsDelivr

WPForms is optional. The child theme includes responsive WPForms styling, but the project can run without it if those forms are not used.

## Installation

1. Install WordPress and the required dependencies.
2. Copy `plugins/starflan-real-estate` into `wp-content/plugins/`.
3. Copy `themes/hello-elementor-child` into `wp-content/themes/`.
4. Activate Estatik.
5. Activate **StarFlan Real Estate Data**.
6. Activate **Hello Elementor Child**.
7. In WordPress, open **Settings → Permalinks** and save once to refresh rewrite rules.

For a shell-based local installation:

```bash
cp -R plugins/starflan-real-estate /path/to/wordpress/wp-content/plugins/
cp -R themes/hello-elementor-child /path/to/wordpress/wp-content/themes/
```

## Site pages

The theme and plugin generate links for several expected paths. Create the corresponding WordPress pages and place the indicated shortcode in each page:

| Page | Suggested path | Shortcode |
|---|---|---|
| Property listings | `/properties/` | `[properties]` |
| Property details | `/single-property/` | `[property]` |
| Cities | `/city/` | `[cities_pagination]` |
| Testimonials | `/testimonials/` | `[testimonials]` |

The current property pagination code uses `/properties-list/` as its base path, while theme city links use `/properties/`. Either create both paths appropriately or update the `home_url()` values to match the final site structure.

The theme also contains links to `/contact-us/`; create that page if the property contact calls to action are used.

## Shortcodes

### Plugin shortcodes

#### Property listing

```text
[properties]
```

Supported query parameters:

```text
/properties/?status=for-sale&city-id=123&sort=lowest_price
```

- `status` is an Estatik `es_status` term slug. It defaults to `for-sale`.
- `city-id` is an `sf_city` post ID.
- `sort` accepts `newest`, `oldest`, `lowest_price`, or `highest_price`.

#### Single property

```text
[property]
```

The property is selected through the URL:

```text
/single-property/?property=456
```

#### Featured property hero

```text
[hero_featured property="456"]
```

### Child-theme shortcodes

```text
[featured_listings]
[cities_slider]
[cities_pagination]
[testimonials]
[page_title]
```

- `[featured_listings]` displays Estatik properties with the `featured` label.
- `[cities_slider]` displays published StarFlan cities in a Swiper carousel.
- `[cities_pagination]` displays six cities per page.
- `[testimonials]` displays six testimonials per page with rating/name sorting.
- `[page_title]` outputs the current WordPress page title.

## Managing data

Activating the plugin adds a **StarFlan Data** menu to the WordPress admin.

### Cities

Each City contains:

- `name` — stored as the post title
- `image` — a WordPress attachment ID
- `properties` — one or more Estatik property post IDs

Properties can be assigned while creating/editing a City or through **Assign Properties to Existing City**. The property picker supports title and ID searches.

### Testimonials

Each Testimonial contains:

- `rating` — a number from 0 through 5
- `name` — the customer name; the frontend falls back to “Anonymous”
- `testimonial` — the testimonial text

### CSV imports

Example files are available in `plugins/starflan-real-estate/samples/`.

City CSV:

```csv
name,image,properties
Makati,101,"501,502"
Manila,102,503
```

Testimonial CSV:

```csv
rating,name,testimonial
5.0,Maria Santos,"The team made the entire property search simple."
4.8,Daniel Reyes,"Every question was answered quickly."
```

Media and property values must be existing WordPress post IDs. Property lists may be separated with commas, semicolons, or whitespace; quote comma-separated values so they remain in one CSV column. Uploads are limited to CSV files no larger than 5 MB and a maximum of 2,000 processed rows.

## Data model

| Record | WordPress post type | Metadata |
|---|---|---|
| City | `sf_city` | `_sf_image_id`, `_sf_estatik_property_ids` |
| Testimonial | `sf_testimonial` | `_sf_rating`, `_sf_name` |
| Estatik property | `properties` | Managed primarily by Estatik |

The StarFlan post types are available in the WordPress REST API. Registered metadata is writable only to users who can edit posts.

The schema can be extended with the `starflan_data_schemas` filter:

```php
add_filter('starflan_data_schemas', function (array $schemas): array {
    $schemas['agent'] = array(
        'label'       => __('Agent', 'starflan-real-estate'),
        'plural'      => __('Agents', 'starflan-real-estate'),
        'post_type'   => 'sf_agent',
        'title_field' => 'name',
        'fields'      => array(
            'name' => array(
                'label'    => __('Name', 'starflan-real-estate'),
                'type'     => 'text',
                'storage'  => 'post_title',
                'required' => true,
            ),
        ),
    );

    return $schemas;
});
```

## Development

There is no JavaScript build step. PHP, CSS, and JavaScript are loaded directly from the plugin and child-theme directories.

The child theme loads:

- Swiper 12 CSS and JavaScript from jsDelivr
- The Hello Elementor parent stylesheet
- Its own stylesheet with a `filemtime()` cache key
- Local ES-module JavaScript from `js/index.js`

### PHP syntax checks

From PowerShell:

```powershell
$files = rg --files plugins themes -g '*.php'
foreach ($file in $files) {
    php -l $file
}
```

From a POSIX shell:

```bash
find plugins themes -name '*.php' -print0 | xargs -0 -n1 php -l
```

### Useful extension points

- `starflan_data_schemas` — add or modify managed record schemas
- `starflan_estatik_property_post_type` — override the Estatik property post type
- `starflan_default_city_image_id` — customize automatic city-image selection
- `starflan_record_created` — react after a managed record is created
- `starflan_record_updated` — react after a managed record is updated
- `starflan_city_properties_assigned` — react after City property assignments change

## Deployment notes

- Back up the WordPress database and uploads before deploying.
- Deploy both the plugin and child theme because the theme consumes the plugin’s City and Testimonial post types.
- Confirm the required page paths after moving between environments.
- Ensure Estatik statuses, labels, galleries, and property fields exist before rendering the property shortcodes.
- Plugin data is intentionally retained when the plugin is deleted.

## License

No license file is currently included. Add an appropriate license before redistributing the project.
