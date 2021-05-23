const CreateAnalysisFile = { template: `
　　　　　　　　　　　　　　　<form action="./index.php" method="POST" enctype="multipart/form-data">
                          <ol style="padding-left:5%;">
                            <strong><li>作成する解析用ファイル名を入力してください（任意）</li></strong>
                            <strong><li>作成する解析用ファイルhead部分の「history」に残すメモを入力(必須)</li></strong>
                            <strong><li>iBELLEs+から出力されたCSVファイル１つ選択してください</li></strong>
                            <strong><li>「解析用ファイル作成」ボタンを押してください</li></strong>
                          </ol>
                         <v-divider></v-divider>
                         <div style="margin-top: 70px;">
                          <div class="memo_wraper">
                            <input class="memo" type="text" name="name" placeholder="作成するファイル名を入力(任意)"/>
                          </div>
                          <div class="memo_wraper">
                            <input class="memo" type="text" name="memo" placeholder="解析用ファイルhead部分の「history」に残すメモを入力(必須)" required/>
                          </div>
                          <table style="padding-left:21%;">
                            <tr><th>iBELLEs出力ファイル(必須)</th><th></th></tr>
                            <tr style="text-align: center;">
                              <td style="text-align: center;">
                                <div class="input_file_wraper">
                                  <input class="input_file" type="file" name="upload_csv" accept=".csv" required/>
                                </div>
                              </td>
                              <td>
                                <div class="btn_wraper">
                                  <button type="submit" class="v-btn v-btn--contained theme--light v-size--default yellow" name="create" value="解析用ファイル作成" style="border-style: solid; border-width: 1px; border-color: #00BFA5;">
                                    <span class="v-btn__content">
                                      <strong>解析用ファイル作成</strong>
                                    </span>
                                  </button>
                                </div>
                              </td>
                            </tr>
                          </table>
                         </div>
                         </form>` }

const CalculateAnalysisFile = { template: `
                         <form action="./analysisSamary.php" method="POST" enctype="multipart/form-data">
                          <ol style="padding-left:5%;">
                            <strong><li>作成する解析後のファイル名を入力してください（任意）</li></strong>
                            <strong><li>解析する解析用CSVファイルを２つ選択してください</li></strong>
                            <strong><li>計算方法を選択してください</li></strong>
                            <strong><li>ノーマライゼーション(100名当たり)を行うかを選択してください</li></strong>
                            <strong><li>「解析」ボタンを押してください</li></strong>
                          </ol>
                          <v-divider></v-divider>
                          <div style="margin-top: 70px;">
                            <div class="memo_wraper">
                            <input class="memo" type="text" name="name" placeholder="作成するファイル名を入力(任意)"/>
                            </div>
                            <table style="padding-left:7%;">
                              <tr><th>１つ目のファイル(必須)</th><th>計算方法</th><th>２つ目のファイル(必須)</th><th></th></tr>
                              <tr style="text-align: center;">
                                  <td style="text-align: center;"><input class="input_file" type="file" name="upload_csv[]" accept=".csv" required/></td>
                                  <td>
                                      <div class="cp_ipselect cp_sl01">
                                          <select name="process" required>
                                              <option value="">未選択</option>
                                              <option value="add"><strong>結合</strong></option>
                                              <option value="substract"><strong>差分</strong></option>
                                          </select>
                                      </div>
                                  </td>
                                  <td><input class="input_file" type="file" name="upload_csv[]" accept=".csv" required/></td>
                                  <td>
                                      <button type="submit" class="v-btn v-btn--contained theme--light v-size--default yellow" name="analysis" value="解析" style="background-color: #FFEB3B;">
                                          <span class="v-btn__content">
                                            <strong>解析</strong>
                                          </span>
                                      </button>
                                  </td>
                              </tr>
                            </table>
                          </div>
                         </form>` }

const routes = [
  { path: '/', component: CreateAnalysisFile },
  { path: '/createAnalysisFile', component: CreateAnalysisFile },
  { path: '/calculateAnalysisFile', component: CalculateAnalysisFile }
]

const router = new VueRouter({
  routes // `routes: routes` の短縮表記
})

const app = new Vue({
    router,
    vuetify: new Vuetify(),
    data () {
      return {
        tab: null,
        dialog: false,
        isPage: true
      }
    },
}).$mount('#app')
