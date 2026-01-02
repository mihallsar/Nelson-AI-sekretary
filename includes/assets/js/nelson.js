(function($){
    'use strict';
    let recognition = null, synthesis = window.speechSynthesis,
        isListening = false, micMode = 'click', finalTranscript = '', currentUtterance = null;

    $(document).ready(function(){
        setTimeout(()=>$('.nelson-bubble-1').addClass('active'),1000);
        setTimeout(()=>$('.nelson-bubble-2').addClass('active'),4000);
        setTimeout(()=>$('.nelson-bubble-3').addClass('active'),7000);
        setTimeout(()=>$('#nelson-close-welcome').addClass('blink'),7500);
        setTimeout(()=>$('#nelson-trigger').addClass('blink-widget'),9000);

        micMode = ('ontouchstart' in window)||(navigator.maxTouchPoints>0)?'hold':'click';

        $('#nelson-welcome-mic').on('click',()=>{$('#nelson-welcome').fadeOut();$('#nelson-assistant').fadeIn();nelsonInitMic();});
        $('#nelson-close-welcome').on('click',()=>$('#nelson-welcome').fadeOut());
        $('#nelson-dont-show').on('change', function(){if(this.checked)document.cookie='nelson_hide_welcome=1; path=/; max-age=31536000';$.post(nelsonData.ajaxUrl,{action:'nelson_hide_welcome',nonce:nelsonData.nonce});});

        $('#nelson-trigger').on('click', ()=>$('#nelson-assistant').fadeIn());
        $('#nelson-close-assistant').on('click',()=>{$('#nelson-assistant').fadeOut();stopSpeak();});

        function nelsonInitMic() {
            let $mic = $('#nelson-main-mic');
            if (micMode==='hold') {
                $mic.on('touchstart mousedown',e=>{e.preventDefault();startListening();});
                $mic.on('touchend mouseup mouseleave',e=>{e.preventDefault();stopListening();});
            } else {
                $mic.on('click',()=>{isListening?stopListening():startListening();});
            }
        }
        window.nelsonInit = nelsonInitMic;
        nelsonInitMic();

        function startListening() {
            finalTranscript='';isListening=true;
            $('.nelson-mic').addClass('listening'); $('#nelson-transcript:visible').text('Слушаю...').show(); showStatus('Говорите');
            if (!recognition) initSpeech();
            recognition&&recognition.start();
        }
        function stopListening() {
            if(!isListening)return; isListening=false;
            $('.nelson-mic').removeClass('listening'); showStatus('Обрабатываю...');
            recognition&&recognition.stop();
            setTimeout(()=>{let cmd=finalTranscript.trim();if(cmd)processCommand(cmd);else showStatus('Нажмите микрофон');},600);
        }
        function processCommand(cmd) {
            $('#nelson-transcript').text("Вы: "+cmd);
            showResponse('<span class="nelson-spinner"></span> Думаю...');
            $.post(nelsonData.ajaxUrl,{action:'nelson_process',nonce:nelsonData.nonce,command:cmd})
                .done(function(resp){
                    if(resp.success&&resp.data){
                        if(resp.data.type==='cards')showCards(resp.data);
                        else showResponse(resp.data.message||'Извините, я еще только учусь и многое не умею, в том числе и этого.');
                        if(resp.data.message)speak(resp.data.message);
                    }else showResponse('Извините, я еще только учусь и многое не умею, в том числе и этого.');
                    showStatus('Готов к следующему запросу');
                }).fail(function(){showResponse('Ошибка связи с сервером.');showStatus('Попробуйте снова');});
        }
        function showCards(data) {
            let html = `<h4 style="margin:0 0 10px;font-size:16px;color:#667eea">${data.title||'Новости'}</h4>`;
            if(data.items && data.items.length) {
                html += data.items.map(post=>`
                    <div class="nelson-post-card">
                        <div class="nelson-post-title">${post.title}</div>
                        <div class="nelson-post-date">📅 ${post.date}</div>
                        <div class="nelson-post-excerpt">${post.excerpt}</div>
                        <div class="nelson-post-actions">
                            <a href="${post.url}" target="_blank" class="nelson-btn">Открыть</a>
                            <button class="nelson-btn nelson-btn-secondary" onclick="window.nelsonReadFull(${post.id})">Прочитать вслух</button>
                        </div>
                    </div>`).join("");
            } else { html+='<p>Информации по запросу нет 😔</p>'; }
            showResponse(html);
        }
        function showResponse(html) { $('#nelson-response').html(html).show(); }
        function showStatus(text) { $('.nelson-status').text(text); }
        function speak(text){
            stopSpeak();
            if(!synthesis)return;
            currentUtterance=new SpeechSynthesisUtterance(text); currentUtterance.lang='ru-RU'; currentUtterance.rate=1.0;
            const voices=synthesis.getVoices();
            currentUtterance.voice=voices.find(v=>v.lang.startsWith('ru'))||null;
            synthesis.speak(currentUtterance);
        }
        function stopSpeak(){ if(synthesis&&synthesis.speaking)synthesis.cancel(); if(currentUtterance)currentUtterance.onend=null; }
        window.nelsonStop = function(){ stopSpeak(); showStatus('Остановлено'); };
        window.nelsonReadFull = function(postId){
            showStatus('Читаю полностью...');
            $.post(nelsonData.ajaxUrl,{action:'nelson_read_full',nonce:nelsonData.nonce,post_id:postId})
                .done(function(resp){ if(resp.success&&resp.data)speak(resp.data.title+'. '+resp.data.content); else speak('Извините, я ещё только учусь и многое не умею, в том числе и этого.'); });
        };
        function initSpeech(){
            if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
                const SR=window.SpeechRecognition||window.webkitSpeechRecognition;
                recognition=new SR();recognition.lang='ru-RU';
                recognition.continuous=true;recognition.interimResults=true;
                recognition.onresult=function(e){
                    let interim='';for(let i=e.resultIndex;i<e.results.length;i++){
                        const txt=e.results[i][0].transcript;
                        if(e.results[i].isFinal)finalTranscript+=txt+' ';else interim+=txt;
                    }
                    $('#nelson-transcript:visible').text(finalTranscript+interim);
                };
                recognition.onerror=function(e){stopListening();showStatus('Ошибка: '+e.error);};
            }
            if(synthesis)synthesis.onvoiceschanged=()=>synthesis.getVoices();
        }
    });
})(jQuery);
