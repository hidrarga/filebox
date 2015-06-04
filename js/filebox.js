$(window).load(function() {
  $('#upload').fineUploader({
    request: {
      inputName: 'file',
      filenameParam: 'filename',
      params: {
        directory: ''
      }
    },
    form: {
      element: $('#upload'),
      autoUpload: false
    },
    text: {
      formatProgress: '{percent}%',
      defaultResponseError: 'error-unknown'
    },
    display: {
      prependFiles: true
    },
    failedUploadTextDisplay: {
        mode: 'custom'
    },
    scaling: {
      hideScaled: true
    },
    debug: false,
    autoUpload: false,
    multiple: true,
    maxConnections: 1,
    callbacks: {
      onSubmitted : function(id, name) {
        var size = formatFileSize(this.getSize(id))
        var item = $(this.getItemByFileId(id))
        
        item.find('.upload-size').text(size)
        item.find('.qq-edit-filename-selector').keypress(function(e) { if(e.which == 13) return false })
        item.find('.qq-upload-cancel-selector').attr('title', _('button-cancel'))
        item.find('.qq-upload-retry-selector').attr('title', _('button-retry'))
      },
      onComplete: function(id, name, response, xhr) {
        var item = $(this.getItemByFileId(id))
        var size = null
        var itemName = item.find('.qq-upload-file-selector')
        
        if(item.hasClass('alert-warning')) {
          item.removeClass('alert-warning')
          itemName.removeClass('qq-editable')
        }
        
        if(response.success) {
          if(item.hasClass('alert-danger'))
            item.removeClass('alert-danger')
          
          item.addClass('alert-success')
          
          itemName.html('<a href="'+response.url+'">'+response.name+'</a>').attr('title', response.name)
          
          size = formatFileSize(response.size)
          
          if(document.websocket)
            document.websocket.send(JSON.stringify({ 
              'url' : response.url, 
              'name' : response.name, 
              'size' : size,
              'type' : 'upload' 
            }))
            
          item.find('.qq-upload-cancel-selector').removeClass('qq-hide')
        }
        else {
          if(!item.hasClass('alert-danger'))
            item.addClass('alert-danger')
          
          size = formatFileSize(this.getSize(id))
          
          item.attr('title', _(response.error))
        }
        
        item.find('.upload-size').text(size)
      },
      onProgress: function(id, name, uploadedBytes, totalBytes) {
        var item = $(this.getItemByFileId(id))
        var percent = parseInt(uploadedBytes / totalBytes * 100, 10)
        
        if(item.hasClass('alert-danger'))
          item.removeClass('alert-danger')
          
        if(!item.hasClass('alert-warning'))
          item.addClass('alert-warning')
          
        item.find('.qq-progress-bar-selector').text(percent + '%')
      },
      onError: function(id, name, reason, xhr) {
        var item = $(this.getItemByFileId(id))
        item.find('.qq-upload-status-text').text(_(reason))
      }
    }
  })
  
  $('#button-reset').click(function() {
    $('#upload').fineUploader('clearStoredFiles')
    return false
  })
  
  $('#form-upload').submit(function() {
    $('#upload').fineUploader('uploadStoredFiles')
    return false
  })
  
  $('.dropdown-menu a').click(function() {    
    $('.dropdown-toggle').children().eq(1).text($(this).text())
  })
})

function formatFileSize(bytes) {
  var precision = 0
  
  if (typeof bytes !== 'number')
    return ''

  if(bytes < 1024)
    return bytes + ' o'
    
  if(bytes < 1048576)
    return (bytes / 1024).toFixed(precision) + ' Kio'
  
  if(bytes < 1073741824)
    return (bytes / 1048576).toFixed(precision) + ' Mio'
  
  return (bytes / 1073741824).toFixed(precision) + ' Gio'
}