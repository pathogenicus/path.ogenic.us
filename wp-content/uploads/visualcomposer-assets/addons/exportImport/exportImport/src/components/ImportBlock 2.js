import React from 'react'
import { getService } from 'vc-cake'

const dataProcessService = getService('dataProcessor')

export default class ImportForm extends React.Component {
  constructor (props) {
    super(props)

    this.state = {
      importing: false,
      statusMessages: [],
      importingDone: false,
      importRequestDone: false,
      errorMessage: ''
    }

    this.handleImportClick = this.handleImportClick.bind(this)
  }

  componentWillUnmount () {
    this.serverProgressRequest.cancelled = true
    this.serverImportRequest.cancelled = true
  }

  handleImportClick (e) {
    e && e.preventDefault()

    const fileInput = document.querySelector('input[name="vcv-file-id"]')
    const fileId = fileInput && fileInput.value

    if (!fileId) {
      return
    }

    this.setState({ importing: true })

    const startImportInner = document.querySelector('.vcv-start-import-inner')
    startImportInner.outerHTML = ''

    this.createServerProgressRequest(fileId)
    this.createServerImportRequest(fileId)
  }

  createServerProgressRequest (fileId) {
    this.serverProgressRequest = dataProcessService.appAdminServerRequest({
      'vcv-action': 'vcv:addon:exportImport:importProgress:adminNonce',
      'vcv-nonce': window.vcvNonce,
      'vcv-time': window.vcvAjaxTime,
      'vcv-file-id': fileId
    }).then((result) => {
      if (this.serverProgressRequest && this.serverProgressRequest.cancelled) {
        this.serverProgressRequest = null
        return
      }
      let response
      try {
        response = JSON.parse(result)
      } catch (e) {
        console.warn('Failed to parse, no valid json.', e)
        const jsonString = this.getJsonFromString(result)
        response = JSON.parse(jsonString)
      }
      const statusMessages = response && response.statusMessages
      if (statusMessages) {
        this.setState({ statusMessages })
      }

      if (!this.state.importRequestDone) {
        window.setTimeout(() => {
          this.createServerProgressRequest(fileId)
        }, 200)
      } else {
        this.setState({ importingDone: true })
      }
    })
  }

  getJsonFromString = (string) => {
    const regex = /(\{"\w+".*\})/g
    const result = string.match(regex)
    if (result) {
      return result[0]
    }
    return false
  }

  createServerImportRequest (fileId) {
    this.serverImportRequest = dataProcessService.appAdminServerRequest({
      'vcv-action': 'vcv:addon:exportImport:continueImport:adminNonce',
      'vcv-nonce': window.vcvNonce,
      'vcv-time': window.vcvAjaxTime,
      'vcv-file-id': fileId
    }).then((result) => {
      if (this.serverImportRequest && this.serverImportRequest.cancelled) {
        this.serverImportRequest = null
        return
      }
      let response
      try {
        response = JSON.parse(result)
      } catch (e) {
        console.warn('Failed to parse, no valid json.', e)
        const jsonString = this.getJsonFromString(result)
        response = JSON.parse(jsonString)
      }
      const newState = { importRequestDone: true }
      const errorMessage = response && response.message
      if (errorMessage) {
        newState.errorMessage = errorMessage
      } else {
        this.createServerProgressRequest(fileId)
      }
      this.setState(newState)
    })
  }

  getBackButton () {
    const localizations = window.VCV_I18N && window.VCV_I18N()
    const backToImportText = localizations ? localizations.backToImport : 'Back to import'
    return (
      <p className='description'>
        <a href={window.vcvBackToImportLink} key='vcvGoBackButton'>{backToImportText}</a>
      </p>
    )
  }

  getStatusMessages () {
    const localizations = window.VCV_I18N && window.VCV_I18N()
    const startImportProcessText = localizations ? localizations.startingImportProcess : 'Starting import process...'

    const messages = []
    messages.push(<p key='vcvImportStatusFirstMessage' className='description'>{startImportProcessText}</p>)
    this.state.statusMessages.forEach((message, index) => {
      messages.push(<p key={`vcvImportStatusMessage${index}`} className='description' dangerouslySetInnerHTML={{ __html: message }} />)
    })
    if (this.state.errorMessage) {
      messages.push(<p key='vcvImportErrorMessage' className='description'><strong dangerouslySetInnerHTML={{ __html: this.state.errorMessage }} /></p>)
    }

    if (this.state.importingDone) {
      messages.push(this.getBackButton())
    }

    return messages
  }

  render () {
    const localizations = window.VCV_I18N && window.VCV_I18N()
    const continueImportText = localizations ? localizations.continueImport : 'Continue importing'

    const importButtons = (
      <>
        <p className='submit'>
          <input type='submit' name='submit' id='vcv-submit' className='button vcv-dashboard-button vcv-dashboard-button--save vcv-dashboard-button--inline' value={continueImportText} onClick={this.handleImportClick} />
        </p>
        {this.getBackButton()}
      </>
    )

    return this.state.importing ? this.getStatusMessages() : importButtons
  }
}
