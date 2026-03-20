(function () {
  function q(id) {
    return document.getElementById(id);
  }

  function syncPanels() {
    var field = q('tdm_content_type');
    if (!field) {
      return;
    }

    var current = field.value;
    document.querySelectorAll('.tdm-type-panel').forEach(function (panel) {
      panel.classList.toggle('is-active', panel.getAttribute('data-tdm-panel') === current);
    });
  }

  function syncFallback() {
    var field = q('tdm_fallback_mode');
    var wrap = document.querySelector('.tdm-fallback-message-wrap');
    if (!field || !wrap) {
      return;
    }

    wrap.style.display = field.value === 'custom' ? '' : 'none';
  }

  function syncWooOptions() {
    var endpointField = q('tdm_woo_endpoint');
    var renderField = q('tdm_woo_render_mode');
    var layoutField = q('tdm_woo_layout');
    if (!endpointField || !renderField || !layoutField || !window.tdmAdmin) {
      return;
    }

    var config = tdmAdmin.wooOptions[endpointField.value] || null;
    if (!config) {
      return;
    }

    var currentMode = renderField.value;
    var initialMode = renderField.getAttribute('data-current') || tdmAdmin.wooConfig.renderMode || 'hybrid';
    var desiredMode = Object.prototype.hasOwnProperty.call(config.renderModes, currentMode) ? currentMode : initialMode;
    if (!Object.prototype.hasOwnProperty.call(config.renderModes, desiredMode)) {
      desiredMode = Object.keys(config.renderModes)[0] || 'hybrid';
    }
    renderField.innerHTML = '';
    Object.keys(config.renderModes).forEach(function (key) {
      var option = document.createElement('option');
      option.value = key;
      option.textContent = config.renderModes[key];
      if (key === desiredMode) {
        option.selected = true;
      }
      renderField.appendChild(option);
    });

    var activeMode = renderField.value;
    var layouts = config.layouts[activeMode] || {};
    var currentLayout = layoutField.value;
    var initialLayout = layoutField.getAttribute('data-current') || tdmAdmin.wooConfig.layout || '';
    var desiredLayout = Object.prototype.hasOwnProperty.call(layouts, currentLayout) ? currentLayout : initialLayout;
    if (!Object.prototype.hasOwnProperty.call(layouts, desiredLayout)) {
      desiredLayout = Object.keys(layouts)[0] || '';
    }
    layoutField.innerHTML = '';
    Object.keys(layouts).forEach(function (key) {
      var option = document.createElement('option');
      option.value = key;
      option.textContent = layouts[key];
      if (key === desiredLayout) {
        option.selected = true;
      }
      layoutField.appendChild(option);
    });

    renderField.setAttribute('data-current', renderField.value);
    layoutField.setAttribute('data-current', layoutField.value);

    document.querySelectorAll('.tdm-field-downloads-only').forEach(function (node) {
      node.style.display = endpointField.value === 'downloads' ? '' : 'none';
    });
  }

  function bindWooEvents() {
    var endpointField = q('tdm_woo_endpoint');
    var renderField = q('tdm_woo_render_mode');
    var layoutField = q('tdm_woo_layout');
    if (endpointField) {
      endpointField.addEventListener('change', function () {
        if (renderField) {
          renderField.setAttribute('data-current', '');
        }
        if (layoutField) {
          layoutField.setAttribute('data-current', '');
        }
        syncWooOptions();
      });
    }
    if (renderField) {
      renderField.addEventListener('change', function () {
        if (endpointField && layoutField && window.tdmAdmin && tdmAdmin.wooOptions[endpointField.value]) {
          layoutField.setAttribute('data-current', '');
          syncWooOptions();
        }
      });
    }
  }

  function bindIconPicker() {
    var search = q('tdm_icon_search');
    var hidden = q('tdm_icon_class');
    var label = q('tdm_icon_label');
    var preview = q('tdm_icon_preview');
    if (!search || !hidden || !label || !preview) {
      return;
    }

    function setActive(icon) {
      hidden.value = icon;
      label.textContent = icon;
      preview.className = icon;
      document.querySelectorAll('.tdm-icon-option').forEach(function (button) {
        button.classList.toggle('is-active', button.getAttribute('data-icon') === icon);
      });
    }

    document.querySelectorAll('.tdm-icon-option').forEach(function (button) {
      button.addEventListener('click', function () {
        setActive(button.getAttribute('data-icon'));
      });
    });

    search.addEventListener('input', function () {
      var term = search.value.toLowerCase();
      document.querySelectorAll('.tdm-icon-option').forEach(function (button) {
        var icon = button.getAttribute('data-icon').toLowerCase();
        button.style.display = icon.indexOf(term) >= 0 ? '' : 'none';
      });
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    var field = q('tdm_content_type');
    if (field) {
      field.addEventListener('change', syncPanels);
    }

    var fallbackField = q('tdm_fallback_mode');
    if (fallbackField) {
      fallbackField.addEventListener('change', syncFallback);
    }

    var renderField = q('tdm_woo_render_mode');
    var layoutField = q('tdm_woo_layout');
    if (renderField && window.tdmAdmin) {
      renderField.setAttribute('data-current', tdmAdmin.wooConfig.renderMode || 'hybrid');
    }
    if (layoutField && window.tdmAdmin) {
      layoutField.setAttribute('data-current', tdmAdmin.wooConfig.layout || '');
    }

    syncPanels();
    syncFallback();
    bindWooEvents();
    syncWooOptions();
    bindIconPicker();
  });
})();
