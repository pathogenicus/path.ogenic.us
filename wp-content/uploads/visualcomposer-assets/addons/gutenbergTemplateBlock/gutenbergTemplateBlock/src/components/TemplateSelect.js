const { Fragment } = window.wp.element
const { InspectorControls } = window.wp.blockEditor
const { PanelBody } = window.wp.components
const { createHigherOrderComponent } = window.wp.compose
const { addFilter } = window.wp.hooks
const customTemplateData = window.VCV_CUSTOM_TEMPLATES && window.VCV_CUSTOM_TEMPLATES()

const isValidBlockType = (name) => {
  const validBlockTypes = [
    'vcv-gutenberg-blocks/template-block'
  ]

  return validBlockTypes.includes(name)
}

export const addMyCustomBlockControls = createHigherOrderComponent((BlockEdit) => {
  const getSelectItems = (templateData) => {
    const items = []
    if (templateData && templateData.length) {
      templateData.forEach((template, index) => {
        if (template.group) {
          const { values, label } = template.group
          if (values) {
            items.push(
              <optgroup label={label} key={`vcwb-custom-template-group-${index}`}>
                {getSelectItems(values)}
              </optgroup>
            )
          }
        } else {
          const value = template.value || 'none'
          items.push(
            <option value={value} key={`vcwb-custom-template-group-${index}-${template.value}`}>
              {template.label}
            </option>
          )
        }
      })
    }
    return items
  }

  return (props) => {
    // If this block supports scheduling and is currently selected, add our UI
    if (isValidBlockType(props.name) && props.isSelected) {
      const templateId = props.attributes.vcwbTemplate || 'none'
      return (
        <Fragment>
          <BlockEdit {...props} />
          <InspectorControls>
            <PanelBody title='Choose your template'>
              <select
                name='template-select'
                id='vcwb-template-select'
                value={templateId}
                style={{ width: '100%' }}
                onChange={(e) => {
                  const value = e && e.currentTarget && e && e.currentTarget.value
                  props.setAttributes({
                    vcwbTemplate: value
                  })
                }}
              >
                {getSelectItems(customTemplateData)}
              </select>
            </PanelBody>
          </InspectorControls>
        </Fragment>
      )
    }

    return <BlockEdit {...props} />
  }
}, 'addMyCustomBlockControls')

addFilter('editor.BlockEdit', 'vcv-gutenberg-blocks/my-control', addMyCustomBlockControls)
