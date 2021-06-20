
# Spirit

Spirit Printer; steganography for 0 cents a copy!

## How to use

TBD; meanwhile, take a look at samples/demo.php!

## Manual

Please note that Spirit has NOT been extensively tested; therefore, consider it a toy.  

SSITU/Sod is REQUIRED to run Spirit.  

Spirit MUST NOT be used to store sensitive data.  
Permanent data loss WILL occurs, AT LEAST in those scenarii:  

- if either Spirit or Sod key gets lost or corrupted, even partially;  
- if a run-time or an encryption error occurs;  
- if libraries are not longer properly maintained.  

Both specified Sod key AND provided Spirit key are REQUIRED for future decryption;  
Spirit key is only given ONCE, on successful image creation;  
thus, Sod key and Spirit key MUST be stored in a safe place.  
Spirit key SHOULD NOT be displayed publicly;  
Sod key MUST NOT be displayed publicly.  

A Spirit image can be duplicated and renamed at will;  
However, a Spirit image MUST NOT be edited, compressed, resized, or saved in another format.  

## Contributing

Sure! You can take a loot at [CONTRIBUTING](CONTRIBUTING.md).

## License

This project is under the MIT License; cf. [LICENSE](LICENSE) for details.