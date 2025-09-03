# Loki_Flatpickr

**This Magento 2 module adds an Alpine.js `x-flatpickr` directive to transform a simple input field into a Flatpickr date picker.**

### Installation
```bash
composer require loki/magento2-flatpickr
bin/magento module:enable Loki_Flatpickr
```

### Usage
To add Flatpickr to a custom HTML input, just add the `x-flatpickr` directive to your HTML element:
```html
<input type="date" x-flatpickr/>
```

If you want to add Flatpickr to a Loki Field Component, add the `x-flatpickr` directive via the XML layout:
```xml
<block name="example" template="Loki_FieldComponents::form/field.phtml">
    <arguments>
        <argument name="input_type" xsi:type="string">date</argument>
        <argument name="field_type" xsi:type="string">text</argument>
        <argument name="field_attributes" xsi:type="array">
            <item name="x-flatpickr" xsi:type="boolean">true</item>
        </argument>
    </arguments>
</block>
```

### Customization
The Flatpickr directive is inserted via a block `loki.directives.flatpickr`. If you want to change the options for Flatpickr, you can use the block argument `options` for this:

```xml
<referenceBlock name="loki.directives.flatpickr">
    <arguments>
        <argument name="options" xsi:type="array">
            <item name="altFormat" xsi:type="string">d-m-Y</item>
        </argument>
    </arguments>
</referenceBlock>
```

For all options, see [https://flatpickr.js.org/options/](https://flatpickr.js.org/options/)

Note that changing the `dateFormat` will also change the value sent to Magento. It is probably best to keep this at its default (`Y-m-d`).
