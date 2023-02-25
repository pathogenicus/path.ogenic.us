import React from 'react'

const { Component } = window.wp.element
const { dispatch, select } = window.wp.data
const customTemplateData = window.VCV_CUSTOM_TEMPLATES && window.VCV_CUSTOM_TEMPLATES()

export default class EditElement extends Component {
  getTemplateName (templateData, id) {
    if (templateData && templateData.length) {
      for (let i = 0; i < templateData.length; i++) {
        const template = templateData[i]

        if (template.group) {
          const { values } = template.group
          if (values && values.length) {
            const foundTemplate = this.getTemplateName(values, id)
            if (foundTemplate) {
              return foundTemplate
            }
          }
        } else {
          const templateValue = template.value || 'none'
          if (parseInt(templateValue) === parseInt(id)) {
            return template
          }
        }
      }
    }
    return null
  }

  handleOpenSidebar () {
    if (dispatch) {
      if (select('core/edit-widgets')) {
        dispatch('core/interface').enableComplementaryArea('core/edit-widgets', 'edit-widgets/block-inspector')
      } else {
        dispatch('core/edit-post').openGeneralSidebar('edit-post/block')
      }
    }
  }

  render () {
    let templateIsSet = false
    const templateId = this.props.attributes.vcwbTemplate || 'none'
    let description = 'No template chosen'
    if (templateId !== 'none') {
      const templateData = this.getTemplateName(customTemplateData, templateId)
      description = templateData ? `Edit '${templateData.label}' or choose another template` : 'Chosen template not found'
      templateIsSet = true
    }

    return (
      <div className='vcv-template-placeholder'>
        <div className='vcv-template-placeholder-head'>
          <svg width='60px' height='45px' viewBox='0 0 60 45' version='1.1' xmlns='http://www.w3.org/2000/svg'>
            <g fill='#BFC5CB' fillRule='nonzero'>
              <path d='M44.3319058,0 L30,8.37451235 L15.6680942,0 L0,9.15474642 L0,27.4707412 L30,45 L60,27.4707412 L60,9.15474642 L44.3319058,0 Z M44.3319058,3.13394018 L55.9785867,9.94148244 L44.3319058,16.7490247 L32.6788009,9.94148244 L44.3319058,3.13394018 Z M17.0107066,19.0962289 L28.6573876,12.2886866 L28.6573876,25.9037711 L17.0107066,32.7113134 L17.0107066,19.0962289 Z M31.3426124,12.2886866 L42.9892934,19.0962289 L42.9892934,32.7113134 L31.3426124,25.9037711 L31.3426124,12.2886866 Z M15.6680942,3.13394018 L27.3147752,9.94148244 L15.6680942,16.7490247 L4.02141328,9.94148244 L15.6680942,3.13394018 Z M2.67880086,25.9037711 L2.67880086,12.2886866 L14.3254818,19.0962289 L14.3254818,32.7113134 L2.67880086,25.9037711 Z M30,41.8660598 L18.3533191,35.0585176 L30,28.2509753 L41.6466809,35.0585176 L30,41.8660598 Z M57.3211991,12.2886866 L57.3211991,25.9037711 L45.6680942,32.7113134 L45.6680942,19.0962289 L57.3211991,12.2886866 Z' />
            </g>
          </svg>
        </div>
        <div className='vcv-template-placeholder-button-container'>
          <button type='button' className='vcv-template-placeholder-button' onMouseDown={this.handleOpenSidebar}>
            {templateIsSet ? 'Edit Template' : 'Choose Template'}
          </button>
        </div>
        <div className='vcv-template-placeholder-footer'>
          {description}
        </div>
      </div>
    )
  }
}
