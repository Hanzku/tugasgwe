'use strict';

const code = document.querySelector('#kd_pelajaran:not([readonly])');
if (code) {
  code.addEventListener('blur', () => { code.value = code.value.trim().toUpperCase(); });
}

const deletion = document.querySelector('[data-delete-form]');
if (deletion) {
  deletion.addEventListener('submit', (event) => {
    if (!window.confirm('Hapus pelajaran ini secara permanen?')) event.preventDefault();
  });
}