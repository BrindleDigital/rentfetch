# Releasing Rent Fetch

Pushing a version tag to GitHub is the release trigger. GitHub Actions validates the
version, checks PHP syntax, runs the standalone regressions, and publishes the
plugin to WordPress.org SVN. No AI agent or local SVN client is needed to run it.
Pushing `main` alone does not publish a release.

## One-time setup

In this repository's **Settings → Secrets and variables → Actions**, add:

- `SVN_USERNAME`: the WordPress.org username of an authorized Rent Fetch committer.
- `SVN_PASSWORD`: that account's SVN-specific password, generated in its
  [WordPress.org account settings](https://profiles.wordpress.org/me/profile/edit/group/3/?screen=svn-password).

Keep these values in GitHub Actions secrets. Never put credentials, temporary
application passwords, or live access details in source files, issues, or logs.
The marketing site's WordPress credentials are separate from WordPress.org SVN.

## Prepare the release

1. Fetch the actual checkout and compare its branch with upstream before editing
   or publishing. Stop for instructions if upstream contains incoming commits;
   preserve existing local work.
2. Update the plugin header and `RENTFETCH_VERSION` in `rentfetch.php`, the
   `Stable tag` and changelog in `readme.txt`, and `changes.md` to the same version.
3. Run the tests relevant to the change, plus the standalone checks in the
   workflow. Monitoring changes also need the Cove integration tests:

   ```sh
   cove wp rent-fetch-main-dev --skip-plugins --skip-themes eval-file "$PWD/tests/test-monitoring-floorplan-errors.php"
   cove wp rent-fetch-main-dev --skip-plugins --skip-themes eval-file "$PWD/tests/test-monitoring-pause.php"
   git diff --check
   ```

   Run these commands from the Rent Fetch plugin checkout in the main dev Cove site.
4. **Remember the marketing-site change notes.** Ask the person releasing the
   update for access to `rentfetch.io` if current authorized access is unavailable.
   When an agent is helping, request credentials through the available secure
   secret-input mechanism; do not request passwords in ordinary chat or save them
   in the repo. Read the site's current connection instructions and command
   schemas before writing.
5. Create the version's release note under the `releasenotes` post type and assign
   the **Rent Fetch** term in the `plugin` taxonomy (currently term ID `22`; verify
   it on the site). Match the existing format and check for duplicates. Save it as
   a **draft while the version is not public**, including compatibility requirements
   and any dependency on a Rent Fetch API update. Other products have separate terms.

## Publish

1. Review and commit the completed changes, then push `main`.
2. When the person responsible for the release is ready to make the version public,
   create and push its tag. For example, replace this example with the actual version:

   ```sh
   git tag 0.39.15
   git push origin refs/tags/0.39.15
   ```

   Both `0.39.15` and `v0.39.15` are accepted. The tag's numeric version must match
   all three version fields. Do not move or re-push an existing release tag.
   For a prepared tag that predates this workflow, or an explicitly authorized
   retry, use **Actions → Publish to WordPress.org → Run workflow** on `main` and
   enter the existing Git tag as `release_tag`. This publishes that tag's exact
   commit; it does not move the Git tag or publish the latest `main` code instead.

3. **Share the direct GitHub Actions run URL immediately after starting a release.**
   Give the person releasing it a clickable link in the progress update so they can
   follow the run without searching GitHub. Do not wait until it finishes to share
   the link. Include the same run link again when reporting the final outcome.
   Check **Actions → Publish to WordPress.org**. The workflow deploys the tagged
   commit, not whatever happens to be on `main` afterward. The existing `.distignore`
   determines the release package; committed runtime assets are shipped without
   rebuilding them. SVN directory assets are left intact unless a `.wordpress-org`
   asset directory is intentionally supplied.
4. Confirm the version and download on the
   [WordPress.org plugin page](https://wordpress.org/plugins/rentfetch/). Directory
   processing can lag behind the successful SVN commit. If the workflow fails,
   inspect the run before retrying; check whether the SVN tag was created first.
5. **Finish the marketing-site update.** Once the release is public, remind the
   person releasing it to publish the prepared change note. Request marketing-site
   access again if it has expired. Publish only when authorized; verify the saved
   version, product taxonomy, public status, and URL, and report the link. If the
   release remains private or is postponed, keep the note as a draft and say so.

Adding this workflow does not publish tags that were already pushed. Automatic tag-triggered runs require the workflow to be present in the tagged
commit. Use the manual existing-tag option above for an older tag; do not move it. Marketing notes are deliberately a separate
human/agent step, not an automatic side effect of the WordPress.org deployment.
