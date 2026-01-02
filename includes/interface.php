<?php if (!defined('ABSPATH')) exit; $nelson_name = get_option('nelson_name', 'Нельсон'); ?>
<!-- ANIMATED WELCOME SCREEN -->
<div id="nelson-welcome" class="nelson-welcome" style="display: <?php echo $show_welcome ? 'flex' : 'none'; ?>;">
  <div class="nelson-welcome-container">

    <!-- CLOSE BUTTON -->
    <button id="nelson-close-welcome" class="nelson-close-btn blink-later">&times;</button>

    <!-- ROBOT CHARACTER -->
    <div class="nelson-robot" id="nelson-robot">
      <div class="nelson-robot-head">
        <div class="nelson-robot-antenna"></div>
        <div class="nelson-robot-eyes">
          <div class="nelson-robot-eye"></div>
          <div class="nelson-robot-eye"></div>
        </div>
        <div class="nelson-robot-mouth"></div>
      </div>
      <div class="nelson-robot-body"></div>
    </div>

    <div class="nelson-robot-name"><?php echo esc_html($nelson_name); ?></div>

    <!-- SPEECH BUBBLES (appear progressively) -->
    <div class="nelson-speech-bubbles">
      <div class="nelson-bubble nelson-bubble-1">
        Привет! Я ИИ секретарь этого сайта <?php echo esc_html($nelson_name); ?>. Чем я могу вам помочь?
      </div>
      <div class="nelson-bubble nelson-bubble-2">
        Если вы не нуждаетесь в моих услугах, можете скрыть это объявление крестиком вверху справа ✕
      </div>
      <div class="nelson-bubble nelson-bubble-3">
        Вы всегда можете вызвать меня виджетом снизу 🎤
      </div>
    </div>

    <!-- BIG MICROPHONE -->
    <div class="nelson-mic-container">
      <button id="nelson-welcome-mic" class="nelson-mic nelson-mic-big">
        <svg width="70" height="70" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
          <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
        </svg>
      </button>
      <div class="nelson-mic-hint">Нажмите и говорите</div>
    </div>
    <div class="nelson-checkbox">
      <label>
        <input type="checkbox" id="nelson-dont-show">
        Больше не показывать
      </label>
    </div>
  </div>
</div>
<!-- MAIN ASSISTANT WINDOW -->
<div id="nelson-assistant" class="nelson-assistant" style="display: none;">
  <div class="nelson-assistant-container">
    <button id="nelson-close-assistant" class="nelson-close-btn">&times;</button>
    <div class="nelson-assistant-header">
      <div class="nelson-avatar">🤵</div>
      <div>
        <h3><?php echo esc_html($nelson_name); ?></h3>
        <span class="nelson-status">● Готов помочь</span>
      </div>
    </div>
    <div class="nelson-mic-container">
      <button id="nelson-main-mic" class="nelson-mic nelson-mic-big">
        <svg width="70" height="70" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
          <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
        </svg>
      </button>
      <div class="nelson-mic-hint">Нажмите для активации</div>
    </div>
    <div id="nelson-transcript" class="nelson-transcript"></div>
    <div id="nelson-response" class="nelson-response"></div>
  </div>
</div>
<!-- FLOATING TRIGGER BUTTON -->
<button id="nelson-trigger" class="nelson-trigger blink-widget">
  <svg width="32" height="32" viewBox="0 0 24 24" fill="white">
    <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
    <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
  </svg>
</button>
