# Directorist - Custom Badge

```
add_action( 'init', function(){
    $my_badge_atts = [
        'id'         => 'my-badge',
        'label'      => 'Badge',
        'icon'       => 'las la-check-circle',
        'hook'       => 'atbdp-my-badge',
        'title'      => 'My Badge',
        'meta_key'   => '_custom-select',
        'meta_value' => 'Free',
        'class'      => 'my-custom-badge'
    ];
    new Directorist_Badge( $my_badge_atts );
} );
```
