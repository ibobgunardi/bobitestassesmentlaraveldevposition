
// BlockUI Helper Module
(function($) {
    // Inject styles only once
    if (!document.getElementById('blockui-styles')) {
      const blockUICSS = `
        .blockui-overlay {
          display: flex;
          justify-content: center;
          align-items: center;
          min-height: 100vh;
          width: 100%;
          text-align: center;
          padding: 2rem;
          box-sizing: border-box;
        }
        /* ... (include all the CSS from previous example) ... */
      `;
      const style = document.createElement('style');
      style.id = 'blockui-styles';
      style.innerHTML = blockUICSS;
      document.head.appendChild(style);
    }
  
    // Attach to window object for global access
    window.AppBlockUI = {
      show: function(options = {}) {
        const defaults = {
          message: 'Processing your request',
          subtext: 'Please wait a moment...',
          spinnerIcon: 'fa-circle-notch',
          overlayOpacity: 0.3,
          fadeIn: 200,
          fadeOut: 400
        };
        const config = { ...defaults, ...options };
  
        $.blockUI({
          message: `
            <div class="blockui-overlay">
              <div class="blockui-content">
                <div class="spinner-container">
                  <i class="fas ${config.spinnerIcon} fa-spin"></i>
                </div>
                <h2 class="blockui-message">${config.message}</h2>
                <p class="blockui-subtext">${config.subtext}</p>
              </div>
            </div>
          `,
          css: {
            backgroundColor: 'rgba(255, 255, 255, 0.95)',
            border: 'none',
            padding: 0,
            color: '#333',
            cursor: 'wait',
            '-webkit-backdrop-filter': 'blur(2px)',
            'backdrop-filter': 'blur(2px)'
          },
          overlayCSS: {
            backgroundColor: '#000',
            opacity: config.overlayOpacity,
            cursor: 'wait'
          },
          baseZ: 2000,
          fadeIn: config.fadeIn,
          fadeOut: config.fadeOut
        });
      },
  
      hide: function() {
        $.unblockUI();
      },
  
      showLoading: function(message = 'Loading...') {
        this.show({
          message: message,
          spinnerIcon: 'fa-spinner'
        });
      },
  
      showProcessing: function(message = 'Processing...') {
        this.show({
          message: message,
          spinnerIcon: 'fa-cog'
        });
      }
    };
    
  })(jQuery);
  

