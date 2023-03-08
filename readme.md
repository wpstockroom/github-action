# WP Stockroom Github action

A Github action to create and upload your selfhosted WordPress themes or plugins.

## Usage

To use this action create a file like `.github/workflows/deployment.yml`.
The following

```yaml
name: Deploy to self hosted WP Stockroom
on:
  release:
    types: [published]
jobs:
  release:
    name: Deploy new Release
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@master
      - name: Build # Remove or modify this step as needed
        run: |
          npm install
          npm run build
          composer install
      - name: Plugin Deploy on Stockroom
        uses: wpstockroom/github-action@main
        env:
          STOCKROOM_URL: ${{ secrets.STOCKROOM_URL }}   # Required, the url to the Stockroom ie. https://wpstockroom.com  
          STOCKROOM_USER: ${{ secrets.STOCKROOM_USER }} # Required, an existing WP user on the STOCKROOM_URL site. Should have editor permissions.
          STOCKROOM_PASS: ${{ secrets.STOCKROOM_PASS }} # Required, an application password, please use separate passwords per theme/plugin.
          BUILD_DIR: the-actual_dir                     # optional, when the actual theme/plugin not in the repo root, but in a subdirectory. 
          SLUG: my-super-cool-plugin                    # optional, remove if GitHub repo name matches WP Stockroom slug, including capitalization

      - name: Attach zip file to release page # Delete this step if you don't want to have a build zip attached.
        uses: svenstaro/upload-release-action@v2
        with:
          repo_token: ${{ secrets.GITHUB_TOKEN }}
          file: ${{ github.workspace }}/*.zip
          file_glob: true
          tag: ${{ github.ref }}
          overwrite: true
```

Add the following [Github secrets](https://docs.github.com/en/actions/security-guides/encrypted-secrets) to the repository.

 - `STOCKROOM_URL` **Required**, the url to the Stockroom ie. https://wpstockroom.com  
 - `STOCKROOM_USER` **Required**, an existing WP user on the STOCKROOM_URL site. Should have editor permissions.
 - `STOCKROOM_PASS` **Required**, an application password, please use separate passwords per theme/plugin.
 - `BUILD_DIR` _optional_, when the actual theme/plugin not in the repo root, but in a subdirectory. 
 - `SLUG` _optional_, remove if GitHub repo name matches WP Stockroom slug, including capitalization

## Excluding files

You can specify files and folders in `.distignore`.  
These are typically files that are only used during development, and not needed for the end user.  
Like `.gitignore`, `node_modules`, `package.json` or `phpcs.xml` Here is decent example of a [.distignore](https://github.com/wpstockroom/wp-stockroom/blob/main/.distignore).

