
const app = express();
app.use(cors({methods: ['GET', 'POST', 'PUT', 'DELETE']
}));
app.use(bodyParser.json());
const port = 3000;
const crypto = require('crypto');
const generateKeys = () => {
    const keys = crypto.generateKeyPairSync('rsa', {
        modulusLength: 2048, // Recommended key size for security
        publicKeyEncoding: {
            type: 'spki', // Recommended for public keys
            format: 'pem',
        },
        privateKeyEncoding: {
            type: 'pkcs8', // Recommended for private keys
            format: 'pem',
        },
    });
    console.log('Private Key:', keys.privateKey);
    console.log('Public Key:', keys.publicKey);
    return keys;
}



let {publicKey, privateKey} = generateKeys();
const decryptData = (base64Data) => {
    // 1. Convert from encoded string to a buffer
    const buffer = Buffer.from(base64Data, 'base64');

    // 2. Explicitly define padding and hash to match the Web Crypto API
    return crypto.privateDecrypt(
        {
            key: privateKey, // Your 2048-bit key from generateKeys()
            oaepHash: "sha256", // MUST BE THIS to match client's "SHA-256"
          },
        buffer
    ).toString("utf8");
};
const urlSafeToBase64 = (urlSafeStr) => {
    // Add padding back for standard Base64 if needed
    let standardB64 = urlSafeStr.replace(/-/g, '+').replace(/_/g, '/');
    while (standardB64.length % 4) {
        standardB64 += '=';
    }
    return standardB64;
};

const users = {};
const admins =
{
    username: 'Mr. Goldstein',
        hash: 'ff5de730fa61e4b9d3ec2298efdce03e24240fb00d45f0d21f4644cda8c85ac4864091d93eefbfbb3b44b11fd6a8107d7f4675f9d4c93fc2503c27b9aa927dc8',
    salt: '19bc8c2e05f668a19bccc5262042af2b'
}
function authenticate(username, password, success, fail){
    if(admins[username]) {
        verifyPassword(password, admins[username].salt, admins[username].hash, () =>success("admin", admins[username]), fail);
        return;
    }
    if(users[username]) {
        verifyPassword(password, users[username].salt, users[username].hash, () =>success("user", users[username]), fail);
        return;
    }
fail();
}
app.get('/public_key', (req, res) => {
res.status(200).send(publicKey)
});
app.post('/user', (req, res) => {
     const username = decryptData(req.body.encryptedUsername);
     const password = decryptData(req.body.encryptedPassword);
     createUser(username, password, (success, message) => {
         if(success){
             res.send(message)
         }
         else{
             res.status(400).send(message);
         }
     })

})
app.put('/data', (req, res) => {
const authUser = decryptData(req.body.encryptedUsername);
const authPass = decryptData(req.body.encryptedPassword);
const newData = decryptData(req.body.encryptedData);
const targetUser = req.query.user;
authenticate(authUser, authPass, (role)=> {
    if(role === "user" && authUser !== targetUser){
        res.status(403).send('Forbidden');
        return;
    }
    if(users[targetUser]) {
        users[targetUser].data = newData;
    }
})

})
app.get('/data', (req, res) => {
    const authUser = decryptData(urlSafeToBase64(req.query.user));
    const authPass = decryptData(urlSafeToBase64(req.query.p));
    const targetUser = req.query.user;

})

function createUser(user, password) {
    const salt = crypto.randomBytes(16).toString('hex');

    crypto.scrypt(password, salt, 64, (err, derivedKey) => {
        if (err) throw err;

        const hash = derivedKey.toString('hex');

        //handle user creation here
    } )

}

function verifyPassword(inputPassword, storedSalt, storedHash, actionOnSuccess, actionOnFail) {
    crypto.scrypt(inputPassword, storedSalt, 64, (err, derivedKey) => {
        if (err) throw err;

        const inputHash = derivedKey.toString('hex');

        // Compare the newly generated hash with the one stored in the database
        if(storedHash === inputHash) {
            actionOnSuccess();
        } else
            actionOnFail();
    });
}
app.listen(() => {
    console.log("Server running!");
})

