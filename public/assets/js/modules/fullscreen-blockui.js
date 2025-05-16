// fullscreen-blockui.js
class FullscreenBlockUI {
    constructor() {
      this.initStyles();
    }
  
    initStyles() {
      if (!document.getElementById('fullscreen-blockui-styles')) {
        const css = `
          .fullscreen-blockui-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(3px);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            opacity: 0;
            transition: opacity 0.3s ease;
          }
  
          .fullscreen-blockui-overlay.active {
            opacity: 1;
          }
  
          .fullscreen-blockui-content {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            min-width: 300px;
            max-width: 90%;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            transform: scale(0.95);
            transition: transform 0.3s ease;
          }
  
          .fullscreen-blockui-overlay.active .fullscreen-blockui-content {
            transform: scale(1);
          }
  
          .fullscreen-blockui-spinner {
            font-size: 3rem;
            color: #3b82f6;
            margin-bottom: 1rem;
          }
  
          .fullscreen-blockui-message {
            font-size: 1.25rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #1f2937;
          }
  
          .fullscreen-blockui-subtext {
            font-size: 0.9rem;
            color: #6b7280;
          }
  
          /* Dark mode support */
          @media (prefers-color-scheme: dark) {
            .fullscreen-blockui-content {
              background: #1e293b;
            }
            .fullscreen-blockui-message {
              color: #f8fafc;
            }
            .fullscreen-blockui-subtext {
              color: #94a3b8;
            }
          }
        `;
        const style = document.createElement('style');
        style.id = 'fullscreen-blockui-styles';
        style.textContent = css;
        document.head.appendChild(style);
      }
    }
  
    show(options = {}) {
      this.hide(); // Remove any existing instance
  
      const config = {
        message: options.message || 'Loading...',
        subtext: options.subtext || '',
        spinnerIcon: options.spinnerIcon || 'fa-spinner'
      };
  
      // Create overlay
      const overlay = document.createElement('div');
      overlay.className = 'fullscreen-blockui-overlay';
      overlay.innerHTML = `
        <div class="fullscreen-blockui-content">
          <div class="fullscreen-blockui-spinner">
            <i class="fas ${config.spinnerIcon} fa-spin"></i>
          </div>
          <div class="fullscreen-blockui-message">${config.message}</div>
          ${config.subtext ? `<div class="fullscreen-blockui-subtext">${config.subtext}</div>` : ''}
        </div>
      `;
  
      document.body.appendChild(overlay);
      
      // Trigger animation
      setTimeout(() => overlay.classList.add('active'), 10);
      
      return overlay;
    }
  
    hide() {
      const overlays = document.querySelectorAll('.fullscreen-blockui-overlay');
      overlays.forEach(overlay => {
        overlay.classList.remove('active');
        setTimeout(() => overlay.remove(), 300); // Match transition duration
      });
    }
  }
  
  // Export a singleton instance
  export const blockUI = new FullscreenBlockUI();