Ответ верни СТРОГО в формате JSON, без markdown-обёрток и без пояснений:
{
"status": "ok",
"code": {{ $successCode ?? 0 }},
"message": "{{ $successMessageDescription ?? 'текст результата' }}"
}

Если по какой-то причине ты не можешь выполнить задание — верни:
{
"status": "error",
"code": {{ $errorCode ?? 1000 }},
"message": "краткое описание причины"
}
