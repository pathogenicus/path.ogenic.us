(function () {
  if (window.vcv) {
    function initializeObserver (filter) {
      if (filter && !filter.hasAttribute('data-vce-state') && (filter.getAttribute('data-vce-collapsible') === 'true')) {
        const resizeObserver = new window.ResizeObserver(entries => {
          for (const entry of entries) {
            const width = entry.contentBoxSize && entry.contentBoxSize[0] ? entry.contentBoxSize[0] : entry.contentRect.width
            if (entry.target && width) {
              const container = entry.target.querySelector('.vce-post-grid-filter-container')
              const children = Array.from(container.children)
              if (children && children.length) {
                let childrenTotalWidth = 0
                children.forEach((child) => {
                  childrenTotalWidth += child.getBoundingClientRect().width
                })
                if (!entry.target.hasAttribute('data-vce-state')) {
                  if (childrenTotalWidth > width) {
                    entry.target.setAttribute('data-vce-state', 'dropdown')
                  } else {
                    entry.target.setAttribute('data-vce-state', 'items')
                  }
                } else if ((childrenTotalWidth > width) && (entry.target.getAttribute('data-vce-state') === 'items')) {
                  entry.target.setAttribute('data-vce-state', 'dropdown')
                } else if ((childrenTotalWidth < width) && (entry.target.getAttribute('data-vce-state') === 'dropdown')) {
                  entry.target.setAttribute('data-vce-state', 'items')
                }
              }
            }
          }
        })
        resizeObserver.observe(filter)
      }
    }

    function getFilterElement (parent) {
      const isVcvhelper = parent && parent.querySelector && parent.querySelector('.vcvhelper')
      if (isVcvhelper) {
        const observer = new window.MutationObserver(mutations => {
          mutations.forEach((mutation) => {
            if (mutation.type === 'attributes' && mutation.attributeName === 'data-vcvs-html') {
              const filter = parent.querySelector('.vce-post-grid-filter-wrapper')
              initializeObserver(filter)
            }
          })
        })
        observer.observe(isVcvhelper, {
          attributes: true
        })
      }
    }

    window.vcv.on('ready', function (action, id, options) {
      if (!action || !id) {
        // do for all
        const filters = document.querySelectorAll('.vce-post-grid-filter-wrapper')
        filters.forEach(filter => initializeObserver(filter))
        return
      }
      const element = document.getElementById(`el-${id}`)
      if (!element) {
        return
      }
      // .vce-posts-grid-wrapper class should be present for all Post Grid elements
      // need to check for class in order to avoid calling getFilterElement function for all elements
      if (element && element.classList.contains('vce-posts-grid-wrapper')) {
        getFilterElement(element)
      }
    })
  }
})()
