<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap">
    <h1>Nelson AI Assistant — Настройки</h1>
    <form method="post" action="">
        <?php wp_nonce_field('nelson_settings'); ?>
        <table class="form-table">
            <tr>
                <th>Имя ассистента</th>
                <td>
                    <input type="text" name="nelson_name" value="<?php echo esc_attr(get_option('nelson_name','Нельсон')); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th>OpenRouter API-ключ</th>
                <td>
                    <input type="text" name="nelson_api_key" value="<?php echo esc_attr(get_option('nelson_api_key','')); ?>" class="regular-text" style="width:420px;">
                    <p class="description">Получить можно на <a href="https://openrouter.ai/keys" target="_blank">openrouter.ai</a></p>
                </td>
            </tr>
            <tr>
                <th>Модель</th>
                <td>
                    <select name="nelson_model" class="regular-text">
                        <option value="openai/gpt-3.5-turbo" <?php selected(get_option('nelson_model'),'openai/gpt-3.5-turbo'); ?>>GPT‑3.5 Turbo (рекомендуется)</option>
                        <option value="openai/gpt-4" <?php selected(get_option('nelson_model'),'openai/gpt-4'); ?>>GPT‑4</option>
                        <!-- Можно добавить другие OpenRouter-модели -->
                    </select>
                </td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" name="nelson_save" class="button button-primary" value="Сохранить">
        </p>
    </form>
</div>
