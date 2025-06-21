<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>ОТЧЕТ</title>
    <link rel="stylesheet" href="{{ asset('css/report.css') }}">
    <style>
    </style>
</head>
<body>
    <h1>ОТЧЕТ</h1>

    <p>
        Информация о методах, вызванных за последние {{ env('REPORT_TIME_INTERVAL', 24) }} часов
    </p>
    <table border="1">
        <thead>
            <tr>
                <th>Название метода</th>
                <th>Количество вызовов</th>
            </tr>
        </thead>
        <tbody>
            @foreach($methodsStats as $methodStat)
                <tr>
                    <td>{{ $methodStat['NAME_METHOD_CONTROLLER'] }}</td>
                    <td>{{ $methodStat['count'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p>Информация о сущностях и их изменениях за последние {{ env('REPORT_TIME_INTERVAL', 24) }} часов</p>
    <table border="1">
        <thead>
            <tr>
                <th>Название сущности</th>
                <th>Количество изменений</th>
            </tr>
        </thead>
        <tbody>
            @foreach($entityStats as $entityStat)
                <tr>
                    <td>{{ $entityStat['entity_type'] }}</td>
                    <td>{{ $entityStat['count'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p>Информация о пользователях и количестве их запросов за последние {{ env('REPORT_TIME_INTERVAL', 24) }} часов</p>
    <table border="1">
        <thead>
            <tr>
                <th>Имя пользователя</th>
                <th>Количество запросов</th>
            </tr>
        </thead>
        <tbody>
            @foreach($userStats[0] as $userStat)
                <tr>
                    <td>{{ $userStat->name }}</td>
                    <td>{{ $userStat->request_count }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p>Информация о пользователях и количестве их авторизаций за последние {{ env('REPORT_TIME_INTERVAL', 24) }} часов"</p>
    <table border="1">
        <thead>
            <tr>
                <th>Имя пользователя</th>
                <th>Количество авторизаций</th>
            </tr>
        </thead>
        <tbody>
            @foreach($userStats[1] as $userStat)
                <tr>
                    <td>{{ $userStat->user_name }}</td>
                    <td>{{ $userStat->counts }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p>Информация о пользователях и количестве их разрешений</p>
    <table border="1">
        <thead>
            <tr>
                <th>Имя пользователя</th>
                <th>Количество разрешений</th>
            </tr>
        </thead>
        <tbody>
            @foreach($userStats[2] as $userStat)
                <tr>
                    <td>{{ $userStat['name'] }}</td>
                    <td>{{ $userStat['permissions'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>