{{ $data['email'] }} 様<br /><br />

いつもFanneezをご利用いただき、誠にありがとうございます。<br />
{{ $data['fan_name'] }} のライブ配信が始まりました。<br /><br />

下記のURLからライブ配信に参加して一緒に楽しみましょう！<br />
<a href="<?php echo env('URL_SEND_MAIL_REGISTER_TEMP') . '/fans/' . $data['nickname'] ?>"><?php echo env('URL_SEND_MAIL_REGISTER_TEMP') . '/fans/' . $data['nickname'] ?></a><br /><br />

※ライブ配信は最長１時間となりますので、お早めにご参加ください。

<br /><br />----------------------------------------------------------------------------<br /><br />

※本メールの無断転載・利用はご遠慮ください。<br />
※本メールのFromアドレスは送信専用となっております。ご返信いただいてもご回答いたしかねます。<br />
※えむゆみファンクラブに関するお問い合わせは以下ご確認の上、サポートセンターまでお問い合わせください。<br /><br />

■よくある質問・お問い合わせ先<br />
お問い合わせ先：info@poooon.com<br />