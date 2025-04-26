# Properties Field

Adds a properties field type

## Requirements

This plugin is tested with Craft CMS 5.7, and PHP 8.3.

## Installation

Add to `composer.json` file in your project root to require this plugin:

Use version `^1.0.0-beta.1` for 'official' releases, or `dev-main` for the latest development version, where anything can go wrong.



```json
{
  "require": {
    "wsydney76/craft-properties-field": "dev-main"
  },
  "minimum-stability": "dev",
  "prefer-stable": true,
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/wsydney76/craft-properties-field"
    }
  ]
}
```

Then run the following commands to install the plugin:

```bash
ddev composer update
ddev craft plugin/install _properties-field
```

## Motivation

Fill a gap in Craft CMS for a field type that allows you to define a set of properties for an element.

While the table field in static rows mode lacks support for restructuring content and different field types, the matrix
field doesn't provide a good UX for this purpose. Craft also has limited support for organizing a field layout in real columns.

This is an extended version of a plugin used for years in a private project, updated to support more field types and
Craft 5 features.

These additions require a beta version of the plugin to be released, as they are not yet fully tested.

No warranty is given, and no support is provided.

Work in progress. Not tested in a multi-site environment.

## Screenshots

### Standard fields

![Field input](field-input.jpg)

![Field settings](field-settings.jpg)

### Use in a matrix block

![Columns settings](column.jpg)

### Group headers and multi-sub-fields properties

![Field input](field-input2.jpg)
![Field settings](field-settings2.jpg)

(This kind of setup is actually one of the main use cases for this plugin, as it allows to add/remove/rearrange properties consistently without creating a myriad of fields/matrix blocks.)

## Storage

The field stores the data posted from the edit form "as-is" in a JSON field (just date fields are converted to ISO format).

This means that all values are stored as strings, including numbers and element ids. Lightswitch values are stored as `"1"` or `""`.

````json
{
  "2563c798-42f7-493c-9bb8-9465a8355a72": {
    "bio": "Nam ipsum risus, rutrum vitae, vestibulum eu, molestie vel, lacus. Donec vitae sapien ut libero venenatis faucibus. Praesent metus tellus, elementum eu, semper a, adipiscing nec, purus. Quisque libero metus, condimentum nec, tempor a, commodo mollis, magna. Mauris turpis nunc, blandit et, volutpat molestie, porta ut, ligula.\r\n\r\nQuisque malesuada placerat nisl. Curabitur turpis. Etiam vitae tortor. Cras non dolor. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu.",
    "born": "1987-04-30T00:00:00+02:00",
    "name": "Erna Klawuppke",
    "image": "942",
    "active": "1",
    "gender": "f",
    "trainer": {
      "isOn": "1",
      "comment": "Offers remote courses on coding"
    },
    "inCompany": {
      "unit": "Years",
      "quantity": "5"
    },
    "mentoring": {
      "isOn": "",
      "comment": "Not yet ready, planned for 10\/2025"
    },
    "relatedPosts": [
      "5231",
      "5215"
    ]
  }
}
````

## Settings

### Plugin settings

* Show header: Whether to show a columns header in the CP. Defaults to `false`
* Date output format. Defaults to `short`
* Entries/Assets view mode. Defaults to `cards`
* Custom input template directory. See Extending section for more details.

### Field settings

A list of properties to be displayed in the field. Each property has the following settings:

* Name: The name of the property
* Handle: The handle of the property (will be built from the name if not set)
* Instructions: Instructions for the property, displayed in a popup via an `info` icon
* Required: Whether the property is required
* Type: The type of the property. The following types are supported:
    * Group header: A group header text
    * Text: A single line text field
    * Textarea: A multi-line text field
    * Number: A number field
    * Email: An email field
    * Boolean: A boolean field (lightswitch)
    * Select: A select field with options
    * Date: A date field
    * Entry/Entries: An entries field with one or multiple entries
    * Asset/Assets: An assets field with one or multiple assets
    * Boolean with comment: A boolean field combined with a comment field (experimental)
    * Dimension: Combines a number field with a text field for the unit (experimental)
    * Dynamic property set. See 'Dynamic property config' below.
* Options: The options for the field. The following options are supported:
    * Select: A list of options for the select field, in the format `value:label`
    * Entry/Entries: A comma-separated list of section handles
    * Asset/Assets:  A comma-separated list of volume handles
    * Dynamic property set: The slug of the entry that holds the property set. See 'Dynamic property config' below.
* Field Config: A JSON string with additional field config settings.

  This is merged into the field config object of the corresponding Craft forms macro, so you can use any settings supported by the field type. For example:
    * `{"placeholder": "placeholder text"}` for a text field
    * `{"offLabel": "labelText","onLabel": "labelText"}` for a boolean field
    * `{"min": 0,"max": 100,"step": 5}` for a number field
  
  Supported for text/email/number, textarea, boolean, select, date, entries/assets property types.
  
  TODO: Take these settings into account when validating the field.


## Dynamic property config

Experimental, work in progress.

Updating the property types config in a custom field settings updates the project config, which means that a deployment action is needed to get this 'live'.

This will fit in most cases, especially when updating the config also requires changes in your templates.

However, sometimes a more dynamic approach is needed, allowing privileged editors to change the property config in the database without touching the project config.

Take the 'Skills' example from the screenshots above. This is a list of skills that can be added/removed/rearranged by the editor, and can be output via a generic template.

Experimental approach:

* Create a new section/entry type with a field layout containing a `propertiesConfig` table field.
* This field matches the field settings, a yaml file is included in the plugin's config folder as a starting point.
  * Copy to `config/project/fields`
  * Run `ddev craft project-config/apply` to update the project config.
* Create a new entry in this section, and add the properties you want to use.
* In the fields settings, create a property type with the type `Dynamic property set` and the slug of the entry that holds the property set.
* This will load the configs from the entry and insert them at that position.

## Limitations

* Only supports a limited set of field types
* Does not support all possible field settings
* Craft is not aware of sub-fields, so the whole field is marked as updated on changes, and a translation method can
  only be used for the whole field, not for sub-fields.
* No out-of-the-box validation for sub-fields.
* No fancy UI for extended sub-field settings.
* Does not support conditional logic.
* No intelligent support for search (yet). For now, the stringified JSON field is thrown into the search index.

## Extending

The plugin can be extended by creating custom property types.

Examples:

![Custom property types](extension.jpg)

```php
<?php

// config/_properties-field.php

return [
    'customInputTemplateDir' => '_properties-field-inputs',
    'extraPropertiesConfig' => [
        'demo' => [
            'label' => 'Demo',
            'type' => 'demo',
            'template' => '_properties-field-inputs/demo.twig',
        ],
        'languages' => [
            'label' => 'Languages',
            'type' => 'languages',
            'template' => '_properties-field-inputs/languages.twig',
        ]
    ],
];

```

Define twig templates inside the folder specified by `customInputTemplateDir` in the plugin settings.

The templates receive the following variables:

* `propertyConfig`: The property config, containing the name, handle, type, and options
* `value`: The value of the property, raw value as stored in the database
* `settings`: The plugin settings

Use multiple inputs with sub-keys for each input:

```twig
{% import '_includes/forms.twig' as forms %}

<div style="padding: 16px 8px 8px 8px; display: flex; align-items: center">
    <div>
        {{ forms.elementSelect({
            name: "#{propertyConfig.handle}[image]",
            elements: value['image'] ? craft.assets.id(value['image']).all : [],
            elementType: 'craft\\elements\\Asset',
            single: true,
            viewMode: 'list'
        }) }}
    </div>

    <div style="margin-left: 8px; width: 100%;">
        {{ forms.text({
            name: "#{propertyConfig.handle}[comment]",
            value: value['comment'] ?? '',
            placeholder: 'Comment'|t,
            class: 'text-combined',
            first: true
        }) }}
    </div>

    <div style="margin-left: 8px;">
        {{ forms.select({
            name: "#{propertyConfig.handle}[select]",
            value: value['select'] ?? '',
            options: [
                {label: 'Option One', value: 'one'},
                {label: 'Option Two', value: 'two'},
                {label: 'Option Three', value: 'three'},
            ],
            first: true
        }) }}
    </div>
</div>
```

Anything that is posted from fields is stored 'as is' in the database json field.

```twig
{% import '_includes/forms.twig' as forms %}

<div style="padding: 16px 8px 8px 8px; ">
    {{ forms.editableTable({
        id: propertyConfig.handle,
        name: propertyConfig.handle,
        addRowLabel: 'Add a language'|t,
        allowAdd: true,
        allowReorder: true,
        allowDelete: true,
        cols: {
            language: {heading: 'Language'|t, type: 'singleline'},
            level: {heading: 'Level'|t, type: 'select', width: '150px', options: [
                {label: 'Native speaker'|t, value: 'native'},
                {label: 'Expert'|t, value: 'expert'},
                {label: 'Beginner'|t, value: 'beginner'},
            ]}
        },
        rows: value
    }) }}
</div>
```

## Templating

The field value is an instance of `wsydney76\propertiesfield\models\PropertiesModel` (or null if not set).

Access the properties directly:

```twig
entry.fieldHandle.properties

entry.fieldHandle.properties['handle']
```

However, this is not recommended, as this reflects the raw database content, where props may be missing or in a wrong
order, e.g. when the field config was updated after an entry was saved.

So always code defensively and check for the existence or type of property before using it.

Loop over all properties:

```twig
{% for prop in entry.fieldHandle.normalizedProperties %}
        {{ prop.name }}:
        {% switch prop.type %}
        {% case "entries" %}
            {% for entry in prop.normalizedValue %}
                {{ entry.link }}
            {% endfor %}
        {% case "asset" %}
            {{ prop.normalizedValue.img({width: 200, height: 200}) }}
        {% case "anythingElseThatNeedsSpecialTreatment" %}   
        ....
        {% default %}
            {{ prop.normalizedValue }}
        {% endswitch %}

{% endfor %}    
```

Ignore empty properties: 

This is especially helpful if new properties are added to the field config, and explicit values are not yet saved.

```twig
{% for prop in props.getNormalizedProperties({ignoreEmpty: true}) %}
   ...
{% endfor %}    
```

TODO: Check, what 'empty' means for the different property types. This may not work currently as expected for all property types.

Alternatively, you can check the raw value of the property, `prop.value` will be `null` if the property is not in the database.


Or set a default value for empty properties:

```twig
{% for prop in props.getNormalizedProperties({ignoreEmpty: true, default: 'n/a'}) %}
   ...
{% endfor %}    
```

TODO: Check, this may throw errors if a property type does not return a string as normalized value.

Each property is an array with the following keys:

* `name`: The name of the property
* `handle`: The handle of the property
* `type`: The type of the property
* `value`: The raw value of the property
* `normalizedValue`: The normalized value of the property, depending on the type:
    * `date`: A formated date string
    * `entry/asset`: A single element (or null)
    * `entries/assets`: An array of elements (or empty array)
    * `select`: An instance of `craft\fields\data\SingleOptionFieldData`. See Craft CMS documentation of the Dropdown field for more details.
    * `other`: The raw value

The config is available via the `propertiesFieldConfig` property:

```twig
entry.fieldHandle.propertiesFieldConfig

craft.app.fields.byHandle('fieldHandle').propertiesFieldConfig

```

A single property can be accessed via the sub-field handle:
These methods return an empty value if the property is not set in the database.

```twig
entry.fieldHandle.get('subfieldHandle') // raw value
entry.fieldHandle.getNormalized('subfieldHandle') // normalized value
```

Entries can be queried via the `hasProp()` entry query method:

```twig
.hasProp('entryTypeHandle', 'fieldHandle', 'subfieldHandle', 'value')

{% set entries = craft.entries
    .hasProp('person', 'personalData', 'age', '33')
.all %}

{% set entries = craft.entries
    .hasProp('propertyTest2', 'skills', 'vue.isOn', '1')
.all
%}

```

TODO: Allow querying for items in arrays (for entries/assets).

The `Entry/Entries/Asset/Assets` sub-field types establish a relation, that can be queried via the `relatedTo` query
param

```twig
{% set entries = craft.entries
    .relatedTo(entry)
.all %}
```

TODO: Check why `.relatedTo({targetElement: 5231, field: 'personalData'})` does not work.

This does not differentiate between the different sub-fields, so all entries selected by any sub-field are returned.

## Roadmap for beta.2

### Merged:

* 'Boolean with comment' property type
* 'Dimension' property type
* Allow customization of property types
* Load input field templates for property types dynamically
* Group Header property type
* Field config settings
* Experimental, wip: Support for dynamic property config

### Todo:

* Support 'required' setting for combined fields
* Support 'normalizedValue' for combined fields
* Better support for building the search index
* Complete translations

