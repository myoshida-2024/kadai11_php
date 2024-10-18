<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>AmiVoice API Speech Recognition Sample</title>
    <script type="text/javascript" src="./scripts/opus-encoder-wrapper.js"></script>
    <script type="text/javascript" src="./scripts/ami-asynchrp.js"></script>
    <script type="text/javascript" src="./scripts/ami-easy-hrp.js"></script>
    <script type="text/javascript" src="./lib/wrp/recorder.js"></script>
    <script type="text/javascript" src="./lib/wrp/wrp.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/p5.js/0.6.1/p5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/p5.js/0.6.1/addons/p5.dom.min.js"></script>
    <script src="rect.js"></script>
    <style>
        * {
            font-family: 'Hiragino Kaku Gothic ProN', 'Helvetica', 'Verdana', 'Lucida Grande', 'ヒラギノ角ゴ ProN', sans-serif;
        }

        a {
            margin-right: 5px;
        }

        button {
            margin: 5px;
        }

        video {
            background: black;
            width: 600px;
        }
    </style>
</head>

<body>
    <table>
        <tbody>
            <tr>
                <td><label for="appKey">APPKEY</label></td>
                <td><input id="appKey" type="password"><br></td>
            </tr>
            <tr>
                <td><label for="audioFile">音声ファイル</label></td>
                <td><input id="audioFile" type="file" accept=".wav,.mp3,.flac,.opus,.m4a,.mp4,.webm"></td>
            </tr>
            <tr>
                <td><label>音声ファイルのオーディオ情報</label></td>
                <td><span id="audioInfo"></span></td>
            </tr>
            <tr>
                <td><label for="engineMode">接続エンジン<label></td>
                <td>
                    <select name="engineMode" id="engineMode">
                        <option value="-a-general">会話_汎用</option>
                        <option value="-a-general-input">音声入力_汎用</option>
                        <option value="-a-medgeneral">会話_医療</option>
                        <option value="-a-medgeneral-input">音声入力_医療</option>
                        <option value="-a-bizmrreport">会話_製薬</option>
                        <option value="-a-bizmrreport-input">音声入力_製薬</option>
                        <option value="-a-medkarte-input">音声入力_電子カルテ</option>
                        <option value="-a-bizinsurance">会話_保険</option>
                        <option value="-a-bizinsurance-input">音声入力_保険</option>
                        <option value="-a-bizfinance">会話_金融</option>
                        <option value="-a-bizfinance-input">音声入力_金融</option>
                        <option value="-a-general-en">英語_汎用</option>
                        <option value="-a-general-zh">中国語_汎用</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td><label for="loggingOptOut">サービス向上のための音声と認識結果の提供を行わない(ログ保存なし)</label></td>
                <td><input type="checkbox" id="loggingOptOut" checked></td>
            </tr>
            <tr>
                <td><label for="keepFillerToken">フィラー単語(言い淀み)を認識結果に含める</label></td>
                <td><input type="checkbox" id="keepFillerToken"></td>
            </tr>
            <tr>
                <td><label for="speakerDiarization">話者ダイアライゼーションを有効にする</label></td>
                <td><input type="checkbox" id="speakerDiarization"></td>
            </tr>
            <tr>
                <td><label for="sentimentAnalysis">感情解析を有効にする(非同期HTTP音声認識APIのみ)</label></td>
                <td><input type="checkbox" id="sentimentAnalysis"></td>
            </tr>
            <tr>
                <td><label for="profileWords">ユーザー登録単語</label></td>
                <td><input type="text" id="profileWords"
                        title="{表記1}{半角スペース}{読み1}|{表記2}{半角スペース}{読み2}のように指定します。例:AmiVoice あみぼいす|猫 きかい"></td>
            </tr>
            <tr>
                <td><label for="useUserMedia">マイクの音声を認識する(WebSocket音声認識API用)</label></td>
                <td><input type="checkbox" id="useUserMedia" checked></td>
            </tr>
            <tr>
                <td><label for="useDisplayMedia">システムの音声を認識する(WebSocket音声認識API用)</label></td>
                <td><input type="checkbox" id="useDisplayMedia"></td>
            </tr>
            <tr>
                <td><label for="useOpusRecorder">音声データをサーバーに送信する前にOgg Opus形式に圧縮する</label></td>
                <td><input type="checkbox" id="useOpusRecorder" checked></td>
            </tr>
            <tr>
                <td colspan="2">
                    <div style="font-size: smaller; margin-left: 20px;">
                        <div>Ogg Opus形式への圧縮には下記のプログラムを使用しています。</div>
                        <div>
                            Opus Recorder License (MIT)<br>
                            Original Work Copyright © 2013 Matt Diamond<br>
                            Modified Work Copyright © 2014 Christopher Rudmin<br>
                            <!-- <a href="https://github.com/chris-rudmin/opus-recorder/blob/v8.0.5/LICENSE.md" -->
                            <!-- <a href="./LICENSE.md" -->
                                <!-- target="_blank" -->
                                <!-- rel="noopener noreferrer">https://github.com/chris-rudmin/opus-recorder/blob/v8.0.5/LICENSE.md</a>                            --> --> -->
                                <a href="./LICENSE.md"
                                target="_blank"
                                rel="noopener noreferrer">LICENSE.mdを開く</a> 
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
    <div>
        <a href="player.html" rel="noopener noreferrer" target="_blank">音声プレイヤーを開く</a>
        <div>
            <button id="executeAsyncButton">非同期HTTP音声認識API実行(音声ファイル)</button><br>
            <button id="startWrpButton">WebSocket音声認識API開始(マイク or システム)</button>
            <button id="stopWrpButton">WebSocket音声認識API停止</button><br>
            <button id="executeHrpButton">同期HTTP音声認識API実行(短い音声ファイル)</button>
        </div>
        <div>
            <textarea id="logs" readonly></textarea>
        </div>
        <script>
            (function () {
                const appKeyElement = document.getElementById("appKey");
                const audioFileElement = document.getElementById("audioFile");
                const audioInfoElement = document.getElementById("audioInfo");
                const engineModeElement = document.getElementById("engineMode");
                const loggingOptOutElement = document.getElementById("loggingOptOut");
                const keepFillerTokenElement = document.getElementById("keepFillerToken");
                const speakerDiarizationElement = document.getElementById("speakerDiarization");
                const sentimentAnalysisElement = document.getElementById("sentimentAnalysis");
                const profileWordsElement = document.getElementById("profileWords");
                const useUserMediaElement = document.getElementById("useUserMedia");
                const useDisplayMediaElement = document.getElementById("useDisplayMedia");
                const useOpusRecorderElement = document.getElementById("useOpusRecorder");

                const executeAsyncButtonElement = document.getElementById("executeAsyncButton");
                const startWrpButtonElement = document.getElementById("startWrpButton");
                const stopWrpButtonElement = document.getElementById("stopWrpButton");
                const executeHrpButtonElement = document.getElementById("executeHrpButton");

                const logsElement = document.getElementById("logs");

                // 音声ファイルを変更したときの処理
                audioFileElement.addEventListener("change", async function (event) {
                    audioInfoElement.textContent = "";
                    const input = event.target;
                    if (input.files.length === 0) {
                        return;
                    }
                    const selectedFile = input.files[0];
                    if (!(/\.(?:wav|mp3|flac|opus|m4a|mp4|webm)$/i.test(selectedFile.name))) {
                        return;
                    }
                    const audioInfo = await getAudioInfo(selectedFile);
                    if (audioInfo !== null) {
                        audioInfoElement.textContent = audioInfo;
                    }
                });

                /**
                 * 音声ファイルの情報を取得します。
                 * @param {File} audioFile 音声ファイル
                 * @returns 音声ファイルの情報
                 */
                async function getAudioInfo(audioFile) {
                    const getAudioInfo_ = function (audioFile) {
                        return new Promise((resolve, reject) => {
                            if (typeof MediaStreamTrackProcessor !== 'undefined') {
                                const videoElement = document.createElement("video");
                                videoElement.width = 0;
                                videoElement.height = 0;
                                videoElement.volume = 0.01;
                                videoElement.autoplay = true;
                                document.body.appendChild(videoElement);
                                videoElement.onplay = function () {
                                    videoElement.onplay = null;
                                    const stream = videoElement.captureStream();
                                    const audioTrack = stream.getAudioTracks()[0];
                                    const processor = new MediaStreamTrackProcessor({ track: audioTrack });
                                    const processorReader = processor.readable.getReader();
                                    processorReader.read().then(function (result) {
                                        if (result.done) {
                                            return;
                                        }
                                        videoElement.pause();
                                        videoElement.currentTime = 0;
                                        stream.getAudioTracks().forEach((track) => {
                                            track.stop();
                                        });
                                        try {
                                            processorReader.cancel();
                                        } catch (e) { }
                                        const audioDuration = videoElement.duration;
                                        URL.revokeObjectURL(videoElement.src);
                                        videoElement.src = "";
                                        document.body.removeChild(videoElement);
                                        resolve(
                                            audioFile.type + " "
                                            + result.value.sampleRate + "Hz "
                                            + result.value.numberOfChannels + "ch "
                                            + Math.floor(audioDuration) + "sec"
                                        );
                                    });
                                };
                                videoElement.src = URL.createObjectURL(audioFile);
                            } else {
                                resolve(null);
                            }
                        });
                    };
                    return await getAudioInfo_(audioFile);
                }

                // 非同期HTTP音声認識APIの実行
                executeAsyncButtonElement.addEventListener("click", function (event) {
                    if (appKeyElement.value.length === 0) {
                        alert("APPKEYを入力してください。");
                        return;
                    }
                    if (audioFileElement.files.length === 0) {
                        alert("音声ファイルを選択してください。");
                        return;
                    }
                    const selectedFile = audioFileElement.files[0];
                    if (!(/\.(?:wav|mp3|flac|opus|m4a|mp4|webm)$/i.test(selectedFile.name))) {
                        alert(".wav,.mp3,.flac,.opus,.m4a,.mp4,.webmファイルを選択してください。");
                        return;
                    }
                    addLog("ジョブの登録処理開始。");
                    const asyncHrp = new AsyncHrp();
                    asyncHrp.onProgress = function (message, sessionId) {
                        addLog((sessionId !== null ? "[" + sessionId + "]" : "") + message);
                    };
                    asyncHrp.onError = function (message, sessionId) {
                        addLog((sessionId !== null ? "[" + sessionId + "]" : "") + message);
                    };
                    asyncHrp.onCompleted = function (resultJson, sessionId) {
                        // addLog(resultJson.text);
                        drawResultView(resultJson, selectedFile);
                    };
                    asyncHrp.engineMode = engineModeElement.value;
                    asyncHrp.loggingOptOut = loggingOptOutElement.checked;
                    asyncHrp.keepFillerToken = keepFillerTokenElement.checked;
                    asyncHrp.speakerDiarization = speakerDiarizationElement.checked;
                    asyncHrp.sentimentAnalysis = sentimentAnalysisElement.checked;
                    asyncHrp.profileWords = profileWordsElement.value.trim();

                    postJob(appKeyElement.value, selectedFile, asyncHrp);
                });

                // 同期HTTP音声認識APIの実行
                executeHrpButtonElement.addEventListener("click", function (event) {
                    if (appKeyElement.value.length === 0) {
                        alert("APPKEYを入力してください。");
                        return;
                    }
                    if (audioFileElement.files.length === 0) {
                        alert("音声ファイルを選択してください。");
                        return;
                    }
                    const selectedFile = audioFileElement.files[0];
                    if (!(/\.(?:wav|mp3|flac|opus|m4a|mp4|webm)$/i.test(selectedFile.name))) {
                        alert(".wav,.mp3,.flac,.opus,m4a,.mp4,.webmファイルを選択してください。");
                        return;
                    }
                    addLog("同期HTTP音声認識API実行。");
                    const easyHrp = new EasyHrp();
                    easyHrp.onError = function (message, sessionId) {
                        addLog((sessionId != null ? "[" + sessionId + "]" : "") + message);
                    };
                    easyHrp.onCompleted = function (resultJson, sessionId) {
                        addLog(resultJson.text);
                        drawResultView(resultJson, selectedFile);
                    };
                    easyHrp.engineMode = engineModeElement.value;
                    easyHrp.loggingOptOut = loggingOptOutElement.checked;
                    easyHrp.keepFillerToken = keepFillerTokenElement.checked;
                    easyHrp.speakerDiarization = speakerDiarizationElement.checked;
                    easyHrp.profileWords = profileWordsElement.value.trim();

                    postJob(appKeyElement.value, selectedFile, easyHrp);
                });

                /**
                 * 非同期/同期 HTTP音声認識API を実行します。
                 * @param {string} appKey APPKEY
                 * @param {File} audioFile 音声ファイル
                 * @param {object} recognizerClient AsyncHrp/EasyHrpオブジェクト
                 */
                function postJob(appKey, audioFile, recognizerClient) {
                    const reader = new FileReader();
                    reader.onload = () => {
                        const AudioContext = window.AudioContext || window.webkitAudioContext;
                        const audioContext = new AudioContext({ sampleRate: 16000 });
                        // 音声ファイルを32bit リニアPCMに変換
                        audioContext.decodeAudioData(reader.result, async function (audioBuffer) {
                            // モノラルにダウンミックス
                            const OfflineAudioContext = window.OfflineAudioContext || window.webkitOfflineAudioContext;
                            const offlineAudioContext = new OfflineAudioContext(audioBuffer.numberOfChannels, audioBuffer.length, audioBuffer.sampleRate);
                            const merger = offlineAudioContext.createChannelMerger(audioBuffer.numberOfChannels);
                            const source = offlineAudioContext.createBufferSource();
                            source.buffer = audioBuffer;

                            for (let i = 0; i < audioBuffer.numberOfChannels; i++) {
                                source.connect(merger, 0, i);
                            }
                            merger.connect(offlineAudioContext.destination);
                            source.start();

                            const mixedBuffer = await offlineAudioContext.startRendering();
                            const float32PcmData = mixedBuffer.getChannelData(0);

                            merger.disconnect();
                            source.disconnect();
                            audioContext.close();

                            if (useOpusRecorderElement.checked) {
                                // モノラルの32bit リニアPCMをOgg Opusに変換
                                const opusEncoderWrapper = new OpusEncoderWrapper();
                                opusEncoderWrapper.originalSampleRate = audioBuffer.sampleRate;
                                opusEncoderWrapper.useStream = false;
                                opusEncoderWrapper.onCompleted = function (opusData) {
                                    const convertedAudioFile = new Blob([opusData], { type: "audio/ogg; codecs=opus" });
                                    // 音声認識を実行
                                    if (typeof recognizerClient.postJob !== 'undefined') {
                                        recognizerClient.postJob(appKey, convertedAudioFile);
                                    }
                                };
                                await opusEncoderWrapper.initialize();
                                opusEncoderWrapper.start();
                                opusEncoderWrapper.encode(float32PcmData);
                                opusEncoderWrapper.stop();
                            } else {
                                // モノラルの32bit リニアPCMを独自ヘッダ付きのDVI/IMA ADPCMに変換
                                const audioFileConverter = new Worker('./scripts/ami-adpcm-worker.js');
                                audioFileConverter.onmessage = (event) => {
                                    const convertedAudioFile = new Blob([event.data], { type: "application/octet-stream" });
                                    audioFileConverter.terminate();
                                    // 音声認識を実行
                                    if (typeof recognizerClient.postJob !== 'undefined') {
                                        recognizerClient.postJob(appKey, convertedAudioFile);
                                    }
                                };
                                audioFileConverter.postMessage([float32PcmData, audioBuffer.sampleRate], [float32PcmData.buffer]);
                            }
                        }, () => {
                            addLog("Can't decode audio data.");
                        });
                    };
                    reader.readAsArrayBuffer(audioFile);
                }

                // WebSocket音声認識APIの実行
                startWrpButtonElement.addEventListener("click", function (event) {
                    if (Wrp.isActive()) {
                        return;
                    }
                    let resultJson = [];
                    Wrp.serverURL = "wss://acp-api.amivoice.com/v1/";
                    if (loggingOptOutElement.checked) {
                        Wrp.serverURL += "nolog/";
                    }
                    Wrp.grammarFileNames = engineModeElement.value;
                    Wrp.authorization = appKeyElement.value;

                    Wrp.profileWords = profileWordsElement.value.trim();
                    Wrp.keepFillerToken = keepFillerTokenElement.checked ? 1 : 0;
                    Wrp.resultUpdatedInterval = 1000;
                    Wrp.checkIntervalTime = 600000;
                    Wrp.segmenterProperties = speakerDiarizationElement.checked ? "useDiarizer=1" : "";

                    Recorder.maxRecordingTime = 3600000;
                    Recorder.sampleRate = 16000;
                    Recorder.downSampling = true;
                    Recorder.adpcmPacking = true;
                    Recorder.useOpusRecorder = useOpusRecorderElement.checked;
                    Recorder.useUserMedia = useUserMediaElement.checked;
                    Recorder.useDisplayMedia = useDisplayMediaElement.checked;

                    Wrp.TRACE = function (message) {
                        if (message.startsWith("ERROR:")) {
                            addLog("WebSocket: " + message);
                        }
                    };
                    Wrp.connectStarted = function () {
                        addLog("WebSocket: 音声認識サーバー接続中...");
                    };
                    Wrp.connectEnded = function () {
                        addLog("WebSocket: 音声認識サーバー接続完了(音声認識準備完了)。");
                    };
                    Wrp.disconnectStarted = function () {
                        addLog("WebSocket: 音声認識サーバー切断中...");
                    };
                    Wrp.disconnectEnded = function () {
                        addLog("WebSocket: 音声認識サーバー切断完了。");
                    };
                    Wrp.resultUpdated = function (result) {
                        const resultJsonPart = JSON.parse(result);

                        // resultを出力する 追加したコード
                        resultJsonPart.segments.forEach(segment => {
                        console.log(`Text: ${segment.text}, Speaker: ${segment.label}, 
                                    Confidence: ${segment.confidence},  start: ${segment.start}, end: ${segment.end},`);
                        });

                        addLog(resultJsonPart.text);
                    };
                    Wrp.resultFinalized = function (result) {
                        const resultJsonPart = JSON.parse(result);
                        addLog(resultJsonPart.text);
                        resultJson.push(resultJsonPart);
                    };
                    Wrp.feedDataPauseEnded = function () {
                        const blob = Recorder.getWaveFile();
                        if (!blob) {
                            return;
                        }
                        drawResultView(resultJson, blob);
                    };
                    Wrp.feedDataResume();
                });
                // WebSocket音声認識APIの停止
                stopWrpButtonElement.addEventListener("click", function (event) {
                    if (!Wrp.isActive()) {
                        return;
                    }
                    Wrp.feedDataPause();
                });

                /**
                 * 認識結果JSONから画面(HTML要素)を構築します
                 * @param {object} resultJson 音声認識結果JSON
                 * @param {object} audioFile 音声ファイル
                 */
                function drawResultView(resultJson, audioFile) {

            //追加コード
            <?php include("funcs.php"); ?>

                    if (!resultJson || !resultJson.segments) {
                        console.error("resultJsonが未定義、または無効です。");
                        return;
                    }
                    // resultJsonの処理（ここに546行目以降のコードを移動）
 // 追加コード
                        
 console.log("488行目に到達"); // この行が表示されるか確認
                        console.log("resultJson:", resultJson); // resultJsonの内容を確認
                        
                        // labelごとの経過時間を合計するためのオブジェクト
                        const labelDurations = {};

                        // 各セグメントをループ処理
                        resultJson.segments.forEach(segment => {
                            segment.results.forEach(result => {
                            result.tokens.forEach(token => {
                            const { label, starttime, endtime } = token;
                            const duration = endtime - starttime;
        

                        // 時間グラフを書く準備でDBにデータを書く
                            if (label == "speaker0") {
                                y = 50;
                                colorR = 200;
                                colorG = 0;
                                colorB = 0;

                            } else if (label == "speaker1") {
                                y = 200;
                                colorR = 0;
                                colorG = 0;
                                colorB = 200;

                            }
                            console.log (starttime, y, duration, 10, colorR, colorG, colorB); // (x, y, width, height, colorR, colorG, colorB);
                            sendData(label,starttime, y, duration, colorR, colorG, colorB);
                            
                            
                            // drawRectangle(starttime, y, duration, 10, colorR, colorG, colorB); // (x, y, width, height, colorR, colorG, colorB) 

                        // labelごとに経過時間を合計
                        if (label in labelDurations) {
                            labelDurations[label] += duration;
                        } else {
                            labelDurations[label] = duration;
                        }
                        });
                        });
                        });
                        // 集計結果を出力
                        console.log("Labelごとの経過時間の合計:", labelDurations);
                        
                       

                      // グラフを書くhtmlにリダイレクト
                        
                        // const encodedData = encodeURIComponent(JSON.stringify(labelDurations));
                        // window.location.href = `graph.html?speaker0=${labelDurations.speaker0}&speaker1=${labelDurations.speaker1}`;

                    const webVttConverter = new Worker('./scripts/ami-webvtt-worker.js');
                    webVttConverter.onmessage = (event) => {
                        const vttSamples = [];
                        vttSamples[0] = event.data;
                        webVttConverter.terminate();

                        // 読み以外の付加情報を取り除いたWEBVTT(スタイル、感情解析の結果、vタグを削除)
                        vttSamples[1] = vttSamples[0].replace(/\nSTYLE[\s\S]+}\n/m, '')
                            .replaceAll(new RegExp('([0-9:.]+ --> [0-9:.]+)(?:.+)', "g"), '$1')
                            .replaceAll(/\n[0-9]+\n[0-9:.]+ --> [0-9:.]+\nENERGY:[0-9]{3} STRESS:[0-9]{3}\n/mg, '')
                            .replaceAll(/<\/?(?:v|[0-9])[^>]*>/g, '');

                        // 読みも取り除いたWEBVTT
                        vttSamples[2] = vttSamples[0].replace(/\nSTYLE[\s\S]+}\n/m, '')
                            .replaceAll(new RegExp('([0-9:.]+ --> [0-9:.]+)(?:.+)', "g"), '$1')
                            .replaceAll(/\n[0-9]+\n[0-9:.]+ --> [0-9:.]+\nENERGY:[0-9]{3} STRESS:[0-9]{3}\n/mg, '')
                            .replaceAll(/<\/?(?:v|[0-9])[^>]*>/g, '')
                            .replaceAll(/<rt>[^<]*<\/rt>/g, '')
                            .replaceAll(/<[^>]*>/g, '');

                        // 読みを取り除かず、読みがあるところは読みだけにしてその前後にスペースを挿入したWEBVTT
                        vttSamples[3] = vttSamples[0].replace(/\nSTYLE[\s\S]+}\n/m, '')
                            .replaceAll(new RegExp('([0-9:.]+ --> [0-9:.]+)(?:.+)', "g"), '$1')
                            .replaceAll(/\n[0-9]+\n[0-9:.]+ --> [0-9:.]+\nENERGY:[0-9]{3} STRESS:[0-9]{3}\n/mg, '')
                            .replaceAll(/<\/?(?:v|[0-9])[^>]*>/g, '')
                            .replaceAll(/<ruby>[^<]*<rt>([^<]*)<\/rt><\/ruby>/g, ' $1 ')
                            .replaceAll(/ {2,}/g, ' ')
                            .replaceAll(/(?: \n|\n )/mg, '\n')
                            .replaceAll(/<[^>]*>/g, '')
                            .replaceAll(/ ([,.?!])/g, '$1');

                        // 認識結果JSONを別ウィンドウで開いたりダウンロードしたりできるようにリンクを作成

                        
                        const links = document.createElement("div");
                        const resultJsonUrl = URL.createObjectURL(
                            new Blob([JSON.stringify(resultJson, null, "\t")], { type: 'application/json;charset=utf-8' }));
                        links.appendChild(createDownloadLink(resultJsonUrl, "JSON", "result.json"));

                        // 認識結果JSONのtextを別ウィンドウで開いたりダウンロードしたりできるようにリンクを作成
                        const linkText = document.createElement("a");
                        let resultText = "";
                        if (typeof resultJson.segments !== 'undefined') {
                            // 非同期HTTP音声認識APIの音声認識結果JSONからsegment毎に改行で区切ったtextを取得
                            resultText = resultJson.segments.map(segment => segment.results[0].text)
                                .filter(value => (typeof value !== 'undefined' && value.length > 0))
                                .join("\n") + "\n";
                        } else {
                            if (Array.isArray(resultJson)) {
                                // WebSocket音声認識APIの音声認識結果JSONを配列にまとめたJSONから発話ごとに改行で区切ったtextを取得
                                resultText = resultJson.map(json => json.text)
                                    .filter(value => (typeof value !== 'undefined' && value.length > 0))
                                    .join("\n") + "\n";
                            } else {
                                // 同期HTTP音声認識APIの音声認識結果JSONからtextを取得
                                resultText = resultJson.text + "\n";
                            }
                        }
                        const resultTextUrl = URL.createObjectURL(
                            new Blob([resultText], { type: 'text/plain;charset=utf-8' }));
                        links.appendChild(createDownloadLink(resultTextUrl, "TEXT", "result.txt"));

                        // 字幕確認用のvideoエレメント作成
                        const player = document.createElement("video");
                        player.src = URL.createObjectURL(audioFile);

                        // videoエレメントの字幕の設定とWebVTTのリンク作成
                        for (let i = 0; i < vttSamples.length; i++) {
                            const track = document.createElement("track");
                            if (i == 0) {
                                track.setAttribute('default', '');
                            }
                            const vttUrl = URL.createObjectURL(
                                new Blob([vttSamples[i]], { type: 'text/vtt;charset=utf-8' }));
                            track.src = vttUrl;
                            track.label = "サンプル" + (i + 1).toString();
                            player.appendChild(track);
                            links.appendChild(createDownloadLink(
                                vttUrl, "WebVTTサンプル" + (i + 1).toString(), "sample" + (i + 1).toString() + ".vtt"));
                        }
                        player.addEventListener("mouseover", function () {
                            this.setAttribute("controls", "");
                        });
                        player.addEventListener("mouseout", function () {
                            this.removeAttribute("controls");
                        });
                        document.body.appendChild(links);
                        document.body.appendChild(player);
                    };
                    webVttConverter.postMessage(resultJson);
                }
               
                       
                /**
                 * ダウンロードリンクを作成します。
                 * @param {string} url URL
                 * @param {string} title タイトル
                 * @param {string} fineName ファイル名
                 * @returns HTMLエレメント
                 */
                function createDownloadLink(url, title, fileName) {
                    const divElement = document.createElement("div");

                    const openLink = document.createElement("a");
                    openLink.href = url;
                    openLink.target = "_blank";
                    openLink.rel = "noopener noreferrer";
                    openLink.textContent = title + "を開く";
                    divElement.appendChild(openLink);

                    const downloadLink = document.createElement("a");
                    downloadLink.href = url;
                    downloadLink.textContent = "ダウンロードする";
                    downloadLink.title = title + "をダウンロード";
                    downloadLink.download = fileName;
                    divElement.appendChild(downloadLink);

                    return divElement;
                }

                /**
                 * ログを出力します。
                 * @param {string} log ログ文字列
                 */
                function addLog(log) {
                    logsElement.textContent += (new Date().toISOString() + " " + log + "\n");
                    setTimeout(function () { logsElement.scrollTop = logsElement.scrollHeight; }, 200);
                }
            })();

            //追加コード
          // sendData関数定義
          function sendData(label,starttime, y, duration, colorR, colorG, colorB) {
            console.log("sendData");
            var data = `label=${label}&starttime=${starttime}&y=${y}&duration=${duration}&colorR=${colorR}&colorG=${colorG}&colorB=${colorB}`;
            console.log("送信データ:", data); // データ内容を確認
            
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "write.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    console.log("データが送信されました");
                    console.log("サーバーからの応答: " + xhr.responseText);
                } else if (xhr.readyState === 4) {
                    console.log("リクエストに失敗しました。ステータス: " + xhr.status);
                }
            };
            
            xhr.send(data); // sendはxhr設定が完了した後に
        }
            



        </script>
</body>

</html>