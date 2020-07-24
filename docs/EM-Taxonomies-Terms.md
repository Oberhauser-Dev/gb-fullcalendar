# Events-Manager

### Taxonomies and Terms

In order to categorize your events more precisely you can add your own taxonomies to events-manager:

Define terms:

```php
const TERMS_AGEGROUP = [
    'event_agegroup_elderly' => ['name' => 'Elderly', 'description' => 'Appropriate for the elderly'],
    'event_agegroup_adult' => ['name' => 'Adults', 'description' => 'Appropriate for adults'],
    'event_agegroup_child' => ['name' => 'Children', 'description' => 'Appropriate for children']
];

const TERMS_GENDER = [
    'event_gender_female' => ['name' => 'female', 'description' => 'Appropriate for females'],
    'event_gender_male' => ['name' => 'male', 'description' => 'Appropriate for males']
];
```

Define taxonomies:

```php
const TXS = [
    'event_agegroup' => ['terms' => TERMS_AGEGROUP, 'ph' => 'EVENTAGEGROUP', 'singular' => 'Age group', 'plural' => 'Age groups'],
    'event_gender' => ['terms' => TERMS_GENDER, 'ph' => 'EVENTGENDER', 'singular' => 'Gender', 'plural' => 'Genders'],
];
```

Function to add terms:

```php
/**
 * Recursive function to add (sub)terms to a taxonomy
 *
 * @param array $txData taxonomy or term data which contains 'terms' array
 * @param string $taxonomy the taxonomy
 * @param null $parentID the term_id of parent (optional)
 */
function insertTaxonomyTerms($txData, $taxonomy, $parentID = null)
{
    if (isset($txData['terms'])) {
        foreach ($txData['terms'] as $termSlug => $termData) {
            if (!get_term_by('slug', $termSlug, $taxonomy)) {
                $args = ['slug' => $termSlug];
                if (isset($termData['description']))
                    $args['description'] = $termData['description'];
                if (isset($parentID))
                    $args['parent'] = $parentID;
                $term = wp_insert_term(
                    $termData['name'],
                    $taxonomy,
                    $args
                );
                insertTaxonomyTerms($termData, $taxonomy, $term['term_id']);
            }
        }
    }
}
```

Function to add taxonomies (with terms):

```php
/**
 * Create taxonomies, => agegroup, gender for the post type EM_POST_TYPE_EVENT
 */
function create_event_taxonomies()
{
    foreach (TXS as $taxonomy => $txData) {
        $singular = $txData['singular'];
        $plural = $txData['plural'];

        $labels = array(
            'name' => _x($plural, 'taxonomy general name', 'textdomain'),
            'singular_name' => _x($singular, 'taxonomy singular name', 'textdomain'),
            'search_items' => __('Search ' . $plural, 'textdomain'),
            'all_items' => __("All", 'textdomain'),
            'parent_item' => __('Parent ' . $singular, 'textdomain'),
            'parent_item_colon' => __('Parent ' . $singular . ':', 'textdomain'),
            'edit_item' => __('Edit ' . $singular, 'textdomain'),
            'update_item' => __('Update ' . $singular, 'textdomain'),
            'add_new_item' => __('Add new ' . $singular . ' hinzu', 'textdomain'),
            'new_item_name' => __('New ' . $singular . ' Name', 'textdomain'),
            'menu_name' => __($singular, 'textdomain'),
        );

        $args = array(
            'hierarchical' => true,
            'labels' => $labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => $taxonomy),
        );

        register_taxonomy($taxonomy, array(EM_POST_TYPE_EVENT, /*EM_POST_TYPE_LOCATION,*/ 'event-recurring'), $args);

        /* Insert terms */
        insertTaxonomyTerms($txData, $taxonomy);
    }
}
```

Add taxonomies & terms in your (child) **theme**'s `functions.php`.

```php
add_action('init', 'create_event_taxonomies', 0);
```
