/**
 * MorgoCMS — EditorJS inicializace
 * Exportuje initEditor(holderId, initialData, pageId) funkci.
 */

import EditorJS from '@editorjs/editorjs';
import Header from '@editorjs/header';
import Paragraph from '@editorjs/paragraph';
import List from '@editorjs/list';
import Quote from '@editorjs/quote';
import Delimiter from '@editorjs/delimiter';
import RawTool from '@editorjs/raw';
import Table from '@editorjs/table';
import Embed from '@editorjs/embed';

/**
 * MorgoImage — vlastní nástroj pro obrázky.
 * Místo přímého uploadu otevře Media Picker modal.
 */
class MorgoImage {
    static get toolbox() {
        return {
            title: 'Obrázek',
            icon: '<svg width="17" height="15" viewBox="0 0 336 276" xmlns="http://www.w3.org/2000/svg"><path d="M291 150V79c0-19-15-34-34-34H79c-19 0-34 15-34 34v42l67-44 81 72 56-29 42 30zm0 52l-43-30-56 30-81-72-67 44v31c0 19 15 34 34 34h178c17 0 31-13 34-29zM79 0h178c44 0 79 35 79 79v118c0 44-35 79-79 79H79c-44 0-79-35-79-79V79C0 35 35 0 79 0z"/></svg>',
        };
    }

    constructor({ data, api, config }) {
        this.api    = api;
        this.config = config || {};
        this.data   = data || {};
        this.wrapper = null;
    }

    render() {
        this.wrapper = document.createElement('div');
        this.wrapper.classList.add('morgo-image-block');

        if (this.data.url) {
            this._showImage();
        } else {
            this._showPicker();
        }

        return this.wrapper;
    }

    _showPicker() {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'morgo-image-picker-btn';
        btn.innerHTML = '+ Vybrat obrázek z médií';
        btn.style.cssText = 'display:block;width:100%;padding:2rem;border:2px dashed #d1d5db;border-radius:0.5rem;cursor:pointer;text-align:center;color:#6b7280;background:none;';

        btn.addEventListener('click', () => this._openPicker());
        this.wrapper.appendChild(btn);
    }

    _showImage() {
        this.wrapper.innerHTML = `
            <div style="position:relative;">
                <img src="${this.data.url}" alt="${this.data.caption || ''}"
                     style="max-width:100%;display:block;border-radius:0.25rem;">
                <input type="text" value="${this.data.caption || ''}"
                       placeholder="Popisek obrázku..."
                       style="margin-top:0.5rem;width:100%;border:none;border-bottom:1px solid #e5e7eb;padding:0.25rem;font-size:0.875rem;color:#6b7280;outline:none;"
                       class="morgo-image-caption">
                <button type="button" style="position:absolute;top:0.25rem;right:0.25rem;background:rgba(0,0,0,0.5);color:white;border:none;border-radius:50%;width:24px;height:24px;cursor:pointer;font-size:12px;"
                        class="morgo-image-remove">✕</button>
            </div>`;

        this.wrapper.querySelector('.morgo-image-caption')?.addEventListener('input', (e) => {
            this.data.caption = e.target.value;
        });

        this.wrapper.querySelector('.morgo-image-remove')?.addEventListener('click', () => {
            this.data = {};
            this.wrapper.innerHTML = '';
            this._showPicker();
        });
    }

    _openPicker() {
        const modal = document.createElement('div');
        modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;display:flex;align-items:center;justify-content:center;';

        const frame = document.createElement('iframe');
        frame.src = this.config.mediaPickerUrl || '/admin/media/picker';
        frame.style.cssText = 'width:90%;max-width:900px;height:80vh;border:none;border-radius:0.5rem;background:white;';

        modal.appendChild(frame);
        document.body.appendChild(modal);

        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.remove();
        });

        // Přijmout vybraný soubor z iframe
        window.addEventListener('message', (event) => {
            if (event.data?.type === 'morgo_media_selected') {
                this.data = {
                    media_id: event.data.id,
                    url:      event.data.url,
                    caption:  event.data.caption || '',
                    alt:      event.data.alt || '',
                };
                this.wrapper.innerHTML = '';
                this._showImage();
                modal.remove();
            }
        }, { once: true });
    }

    save() {
        return this.data;
    }

    validate(data) {
        return !!data.url;
    }
}

/**
 * Inicializuje EditorJS editor.
 * @param {string} holderId - ID elementu pro editor
 * @param {object} initialData - Počáteční data (EditorJS JSON)
 * @param {number|null} pageId - ID stránky pro autosave
 */
export function initEditor(holderId, initialData, pageId = null) {
    const editor = new EditorJS({
        holder: holderId,
        placeholder: 'Začněte psát nebo vyberte blok...',
        tools: {
            header: {
                class: Header,
                inlineToolbar: true,
                config: { levels: [1, 2, 3, 4, 5, 6], defaultLevel: 2 },
            },
            paragraph: {
                class: Paragraph,
                inlineToolbar: true,
            },
            image: {
                class: MorgoImage,
                config: { mediaPickerUrl: '/admin/media/picker' },
            },
            list: {
                class: List,
                inlineToolbar: true,
            },
            quote: {
                class: Quote,
                inlineToolbar: true,
            },
            delimiter: Delimiter,
            raw:       RawTool,
            table:     Table,
            embed:     Embed,
        },
        data: initialData || {},
        onChange: () => scheduleAutosave(editor, pageId),
    });

    window.morgoEditor = editor;

    // Autosave každých 30 sekund
    let autosaveTimer = null;

    function scheduleAutosave(ed, id) {
        clearTimeout(autosaveTimer);
        autosaveTimer = setTimeout(() => doAutosave(ed, id), 30000);
    }

    async function doAutosave(ed, id) {
        if (!id) return;
        try {
            const data  = await ed.save();
            const csrf  = document.querySelector('meta[name="csrf-token"]')?.content || '';
            await fetch('/admin/pages/draft', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrf },
                body:    JSON.stringify({ id, content_blocks: JSON.stringify(data) }),
            });
            const status = document.getElementById('autosave-status');
            if (status) {
                status.textContent = 'Automaticky uloženo ' + new Date().toLocaleTimeString('cs');
            }
        } catch (e) {
            console.warn('Autosave failed', e);
        }
    }

    return editor;
}
